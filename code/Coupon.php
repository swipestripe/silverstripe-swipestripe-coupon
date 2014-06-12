<?php
/**
 * Coupon rates that can be set in {@link SiteConfig}. Several flat rates can be set 
 * for any supported shipping country.
 */
class Coupon extends DataObject implements PermissionProvider {
	
	/**
	 * Fields for this tax rate
	 * 
	 * @var Array
	 */
	private static $db = array(
		'Title' => 'Varchar',
		'Code' => 'Varchar',
		'Discount' => 'Decimal(18,2)',
		'Expiry' => 'Date'
	);
	
	/**
	 * Coupon rates are associated with SiteConfigs.
	 * 
	 * @var unknown_type
	 */
	private static $has_one = array(
		'ShopConfig' => 'ShopConfig'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'Code' => 'Code',
		'SummaryOfDiscount' => 'Discount',
		'Expiry' => 'Expiry'
	);

    public function providePermissions()
    {
        return array(
            'EDIT_COUPONS' => 'Edit Coupons',
        );
    }

    public function canEdit($member = null)
    {
        return Permission::check('EDIT_COUPONS');
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canDelete($member = null)
    {
        return Permission::check('EDIT_COUPONS');
    }

    public function canCreate($member = null)
    {
        return Permission::check('EDIT_COUPONS');
    }
	
	/**
	 * Field for editing a {@link Coupon}.
	 * 
	 * @return FieldSet
	 */
	public function getCMSFields() {

		return new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('CouponRate',
					TextField::create('Title', _t('Coupon.TITLE', 'Title')),
					TextField::create('Code', _t('Coupon.CODE', 'Code')),
					NumericField::create('Discount', _t('Coupon.DISCOUNT', 'Coupon discount'))
						->setRightTitle('As a percentage (%)'),
					DateField::create('Expiry')
						->setConfig('showcalendar', true)
				)
			)
		);
	}
	
	/**
	 * Label for using on {@link CouponModifierField}s.
	 * 
	 * @see CouponModifierField
	 * @return String
	 */
	public function Label() {
		return $this->Title . ' ' . $this->SummaryOfDiscount() . ' discount';
	}
	
	/**
	 * Summary of the current tax rate
	 * 
	 * @return String
	 */
	public function SummaryOfDiscount() {
		return $this->Discount . ' %';
	}

	public function Amount($order) {

		// TODO: Multi currency

		$shopConfig = ShopConfig::current_shop_config();

		$amount = new Price();
		$amount->setCurrency($shopConfig->BaseCurrency);
		$amount->setSymbol($shopConfig->BaseCurrencySymbol);

		$total = $order->SubTotal()->getAmount();
		$mods = $order->TotalModifications();

		if ($mods && $mods->exists()) foreach ($mods as $mod) {
			if ($mod->ClassName != 'CouponModification') {
				$total += $mod->Amount()->getAmount();
			}
		}
		$amount->setAmount(- ($total * ($this->Discount / 100)));

		return $amount;
	}

	/**
	 * Display price, can decorate for multiple currency etc.
	 * 
	 * @return Price
	 */
	public function Price($order) {
		
		$amount = $this->Amount($order);
		$this->extend('updatePrice', $amount);
		return $amount;
	}
	
}

/**
 * So that {@link Coupon}s can be created in {@link SiteConfig}.
 */
class Coupon_Extension extends DataExtension {

	/**
	 * Attach {@link Coupon}s to {@link SiteConfig}.
	 * 
	 * @see DataObjectDecorator::extraStatics()
	 */
	public static $has_many = array(
		'Coupons' => 'Coupon'
	);
}

class Coupon_Admin extends ShopAdmin {

	private static $tree_class = 'ShopConfig';

	private static $allowed_actions = array(
		'CouponSettings',
		'CouponSettingsForm',
		'saveCouponSettings'
	);

	private static $url_rule = 'ShopConfig/Coupon';
	protected static $url_priority = 100;
	private static $menu_title = 'Shop Coupons';

	private static $url_handlers = array(
		'ShopConfig/Coupon/CouponSettingsForm' => 'CouponSettingsForm',
		'ShopConfig/Coupon' => 'CouponSettings'
	);

	public function init() {
		parent::init();
		$this->modelClass = 'ShopConfig';
	}

	public function Breadcrumbs($unlinked = false) {

		$request = $this->getRequest();
		$items = parent::Breadcrumbs($unlinked);

		if ($items->count() > 1) $items->remove($items->pop());

		$items->push(new ArrayData(array(
			'Title' => 'Coupon Settings',
			'Link' => $this->Link(Controller::join_links($this->sanitiseClassName($this->modelClass), 'Coupon'))
		)));

		return $items;
	}

	public function SettingsForm($request = null) {
		return $this->CouponSettingsForm();
	}

	public function CouponSettings($request) {

		if ($request->isAjax()) {
			$controller = $this;
			$responseNegotiator = new PjaxResponseNegotiator(
				array(
					'CurrentForm' => function() use(&$controller) {
						return $controller->CouponSettingsForm()->forTemplate();
					},
					'Content' => function() use(&$controller) {
						return $controller->renderWith('ShopAdminSettings_Content');
					},
					'Breadcrumbs' => function() use (&$controller) {
						return $controller->renderWith('CMSBreadcrumbs');
					},
					'default' => function() use(&$controller) {
						return $controller->renderWith($controller->getViewer('show'));
					}
				),
				$this->response
			); 
			return $responseNegotiator->respond($this->getRequest());
		}

		return $this->renderWith('ShopAdminSettings');
	}

	public function CouponSettingsForm() {

		$shopConfig = ShopConfig::get()->First();

		$fields = new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('Coupon',
					GridField::create(
						'Coupons',
						'Coupons',
						$shopConfig->Coupons(),
						GridFieldConfig_HasManyRelationEditor::create()
					)
				)
			)
		);

		$actions = new FieldList();
		$actions->push(FormAction::create('saveCouponSettings', _t('GridFieldDetailForm.Save', 'Save'))
			->setUseButtonTag(true)
			->addExtraClass('ss-ui-action-constructive')
			->setAttribute('data-icon', 'add'));

		$form = new Form(
			$this,
			'EditForm',
			$fields,
			$actions
		);

		$form->setTemplate('ShopAdminSettings_EditForm');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm');
		$form->addExtraClass('cms-content cms-edit-form center ss-tabset');
		if($form->Fields()->hasTabset()) $form->Fields()->findOrMakeTab('Root')->setTemplate('CMSTabSet');
		$form->setFormAction(Controller::join_links($this->Link($this->sanitiseClassName($this->modelClass)), 'Coupon/CouponSettingsForm'));

		$form->loadDataFrom($shopConfig);

		return $form;
	}

	public function saveCouponSettings($data, $form) {

		//Hack for LeftAndMain::getRecord()
		self::$tree_class = 'ShopConfig';

		$config = ShopConfig::get()->First();
		$form->saveInto($config);
		$config->write();
		$form->sessionMessage('Saved Coupon Settings', 'good');

		$controller = $this;
		$responseNegotiator = new PjaxResponseNegotiator(
			array(
				'CurrentForm' => function() use(&$controller) {
					//return $controller->renderWith('ShopAdminSettings_Content');
					return $controller->CouponSettingsForm()->forTemplate();
				},
				'Content' => function() use(&$controller) {
					//return $controller->renderWith($controller->getTemplatesWithSuffix('_Content'));
				},
				'Breadcrumbs' => function() use (&$controller) {
					return $controller->renderWith('CMSBreadcrumbs');
				},
				'default' => function() use(&$controller) {
					return $controller->renderWith($controller->getViewer('show'));
				}
			),
			$this->response
		); 
		return $responseNegotiator->respond($this->getRequest());
	}

	public function getSnippet() {

		if (!$member = Member::currentUser()) return false;
		if (!Permission::check('CMS_ACCESS_' . get_class($this), 'any', $member)) return false;

		return $this->customise(array(
			'Title' => 'Coupon Management',
			'Help' => 'Create coupons',
			'Link' => Controller::join_links($this->Link('ShopConfig'), 'Coupon'),
			'LinkTitle' => 'Edit coupons'
		))->renderWith('ShopAdmin_Snippet');
	}

}

class Coupon_OrderExtension extends DataExtension {

	/**
	 * Attach {@link Coupon}s to {@link SiteConfig}.
	 * 
	 * @see DataObjectDecorator::extraStatics()
	 */
	public static $db = array(
		'CouponCode' => 'Varchar'
	);
}

class Coupon_CheckoutFormExtension extends Extension {

	public function getCouponFields() {

		$fields = new FieldList();
		$fields->push(Coupon_Field::create('CouponCode', _t('Coupon.COUPON_CODE_LABEL', 'Enter your coupon code'))
			->setForm($this->owner)
		);
		return $fields;
	}
}

class Coupon_Field extends TextField {

	public function FieldHolder($properties = array()) {
		
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('swipestripe-coupon/javascript/CouponModifierField.js');
		return $this->renderWith('CouponField');
	}
}


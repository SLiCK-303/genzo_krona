<?php

require_once _PS_MODULE_DIR_ . 'genzo_krona/classes/ActionOrder.php';

use KronaModule\ActionOrder;

class AdminGenzoKronaOrdersController extends ModuleAdminController
{

    /**
     * @var ActionOrder object
     */
    protected $object;
    public $total_name;

    public function __construct() {

        $this->module = 'genzo_krona';
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'KronaModule\ActionOrder';
        $this->table = 'genzo_krona_action_order';
        $this->identifier = 'id_action_order';
        $this->lang = false;

        $this->_select = 'c.name';
        $this->_join = 'LEFT JOIN '._DB_PREFIX_.'currency c ON (c.id_currency = a.id_currency) ';

        Shop::addTableAssociation($this->table, array('type' => 'shop'));

        $fields_list = array(
            'id_action_order' => array(
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_type' => 'int',
            ),
            'name' => array(
                'title' => $this->l('Currency'),
                'align' => 'left',
            ),
            'coins_change' => array(
                'title' => $this->l('Coins Change'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_type' => 'int'
            ),
            'minimum_amount' => array(
                'title' => $this->l('Minimum Amount'),
                'align' => 'left',
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'type'  => 'bool',
                'filter_type' => 'int',
            )
        );

        $this->fields_list = $fields_list;
        $this->actions = array('edit');
        $this->_orderBy = 'id_action_order';
        $this->_orderWay = 'ASC';
        $this->bulk_actions = [];

        // Configuration
        $id_lang = $this->context->language->id;
        $id_shop_group = $this->context->shop->id_shop_group;
        $id_shop = $this->context->shop->id_shop;

        $this->total_name = Configuration::get('krona_total_name', $id_lang, $id_shop_group, $id_shop);

        parent::__construct();

    }

    public function renderList() {
        ActionOrder::checkCurrencies();

        return parent::renderList();
    }

    public function initContent() {

        // Some Basic Display functions
        $this->initTabModuleList();
        $this->initToolbar();
        $this->initPageHeaderToolbar();

        if ($this->display == 'edit') {
            if (!$this->loadObject(true)) {
                return;
            }
            $this->content = $this->renderForm();
        }
        else {
            $this->content = $this->renderList();
        }

        // This are the real smarty variables
        $this->context->smarty->assign(
            array(
                'content'   => $this->content,
                'tab'       => 'Orders',
                'show_page_header_toolbar'  => $this->show_page_header_toolbar,
                'page_header_toolbar_title' => $this->page_header_toolbar_title,
                'page_header_toolbar_btn'   => $this->page_header_toolbar_btn,
            )
        );

        $tpl = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'genzo_krona/views/templates/admin/main.tpl');

        $this->context->smarty->assign(array(
            'content' => $tpl, // This seems to be anything inbuilt. It's just chance that we both use content as an assign variable
        ));

    }

    public function initToolbar() {
        parent::initToolbar();
        unset( $this->toolbar_btn['new'] ); // To remove the add button
    }

    public function renderForm() {

        $inputs[] = array(
            'type' => 'hidden',
            'name' => 'id_action_order'
        );
        $inputs[] = array(
            'type'         => 'html',
            'name'         => 'html_note',
            'html_content' => "<p>{$this->l('Note: The other settings like order status, title or message can be set globaly under \"Settings\".')}</p>",
        );
        $inputs[] = array(
            'type' => 'switch',
            'label' => $this->l('Active'),
            'name' => 'active',
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
        );
        $inputs[] = array(
            'type'  => 'text',
            'name'  => 'coins_change',
            'label' => $this->l('Coins reward'),
            'desc'  => sprintf($this->l('Example: For every %s spent, the user will get X coins.'),$this->object->currency),
            'class'  => 'input fixed-width-sm',
            'suffix' => $this->l('Coins').'/'.$this->object->currency_iso,
        );
        $inputs[] = array(
            'type'  => 'text',
            'name'  => 'minimum_amount',
            'label' => $this->l('Minimum Amount'),
            'desc'  => sprintf($this->l('Needs there to be a minimum amount of %s to get coins? If not, set it equal to 0.'), $this->object->currency),
            'class'  => 'input fixed-width-sm',
            'suffix' => $this->object->currency_iso,
        );
        $inputs[] = array(
            'type'  => 'text',
            'name'  => 'coins_conversion',
            'label' => $this->l('Coins conversion'),
            'desc'  => sprintf($this->l('Example: For every coin a customer collected, he can generate a voucher with the value of X %s.'),$this->object->currency),
            'class'  => 'input fixed-width-sm',
            'suffix' => $this->object->currency_iso.'/'.$this->l('Coins'),
        );

        if (Shop::isFeatureActive()) {
            $inputs[] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association:'),
                'name' => 'checkBoxShopAsso',
            );
        }

        $fields_form = array(
            'legend' => array(
                'title' => $this->l('Edit Order Action:'). ' '. $this->object->currency,
                'icon' => 'icon-cogs'
            ),
            'input' => $inputs,
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );


        // Fix of values since we dont use always same names
        $this->fields_form = $fields_form;

        $this->tpl_form_vars = array(
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->default_form_language = $this->context->language->id;

        return parent::renderForm();
    }

    public function setMedia() {

        parent::setMedia();

        $this->addJS(array(
            _MODULE_DIR_.'genzo_krona/views/js/admin-krona.js',
        ));

        $this->addCSS(array(
            _MODULE_DIR_.'genzo_krona/views/css/admin-krona.css',
        ));

    }

}

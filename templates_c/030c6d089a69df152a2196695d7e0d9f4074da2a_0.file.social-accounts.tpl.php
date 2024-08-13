<?php
/* Smarty version 3.1.48, created on 2024-04-17 12:52:02
  from '/home/murabba/public_html/whmcs.murabba.dev/templates/twenty-one/includes/social-accounts.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.48',
  'unifunc' => 'content_661fc5f261b2f9_19539719',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '030c6d089a69df152a2196695d7e0d9f4074da2a' => 
    array (
      0 => '/home/murabba/public_html/whmcs.murabba.dev/templates/twenty-one/includes/social-accounts.tpl',
      1 => 1709647280,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_661fc5f261b2f9_19539719 (Smarty_Internal_Template $_smarty_tpl) {
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['socialAccounts']->value, 'account');
$_smarty_tpl->tpl_vars['account']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['account']->value) {
$_smarty_tpl->tpl_vars['account']->do_else = false;
?>
    <li class="list-inline-item">
        <a class="btn btn-icon mb-1" href="<?php echo $_smarty_tpl->tpl_vars['account']->value->getUrl();?>
" target="_blank">
            <i class="<?php echo $_smarty_tpl->tpl_vars['account']->value->getFontAwesomeIcon();?>
"></i>
        </a>
    </li>
<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
}
}

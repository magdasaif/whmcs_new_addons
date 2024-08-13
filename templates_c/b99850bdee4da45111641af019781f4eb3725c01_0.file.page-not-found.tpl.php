<?php
/* Smarty version 3.1.48, created on 2024-04-17 12:52:02
  from '/home/murabba/public_html/whmcs.murabba.dev/templates/twenty-one/error/page-not-found.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.48',
  'unifunc' => 'content_661fc5f2658b13_76898110',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b99850bdee4da45111641af019781f4eb3725c01' => 
    array (
      0 => '/home/murabba/public_html/whmcs.murabba.dev/templates/twenty-one/error/page-not-found.tpl',
      1 => 1709647280,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_661fc5f2658b13_76898110 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="container">
    <div class="text-center p-5">

        <i class="fas fa-exclamation-circle display-1 font-weight-bold text-primary"></i>
        <h1 class="display-1 font-weight-bold text-primary line-height-reduced mb-5">
            <?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['lang'][0], array( array('key'=>"errorPage.404.title"),$_smarty_tpl ) );?>

        </h1>
        <h3><?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['lang'][0], array( array('key'=>"errorPage.404.subtitle"),$_smarty_tpl ) );?>
</h3>
        <p><?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['lang'][0], array( array('key'=>"errorPage.404.description"),$_smarty_tpl ) );?>
</p>

        <div class="buttons">
            <a href="<?php echo $_smarty_tpl->tpl_vars['systemurl']->value;?>
" class="btn btn-primary px-4">
                <?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['lang'][0], array( array('key'=>"errorPage.404.home"),$_smarty_tpl ) );?>

            </a>
            <a href="<?php echo $_smarty_tpl->tpl_vars['systemurl']->value;?>
contact.php" class="btn btn-info px-4">
                <?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['lang'][0], array( array('key'=>"errorPage.404.submitTicket"),$_smarty_tpl ) );?>

            </a>
        </div>

    </div>
</div>
<?php }
}

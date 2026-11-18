<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_a11y" class="sr--no--padding" style="width:520px">
    <sr-modal-header>
        <h2 class="sr--modal--title sr--modal--title--simple"><?php echo __('Accessibility Check','revslider');?></h2>
        <sr-modal-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-modal-close>
    </sr-modal-header>
    <sr-modal-content>
        <sr-wrap class="sr--p--20--15">
            <sr-wrap id="sr_a11y_result"></sr-wrap>
        </sr-wrap>
    </sr-modal-content>
</sr-modal>

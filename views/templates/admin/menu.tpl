{**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<div id="modulecontent" class="clearfix">
    <div id="studentdiscount-menu">
        <div class="col-lg-2">
            <div class="list-group" v-on:click.prevent>
                <a href="#" class="list-group-item" onClick="changeSection('setting')"> {l s='Setting' mod='studentdiscounts'}</a>
                <a href="#" class="list-group-item" onClick="changeSection('studentDomains')"> {l s='Student Domains' mod='studentdiscounts'}</a>
                <a href="#" class="list-group-item" onClick="changeSection('studentEmailVerification')"> {l s='Verification students' mod='studentdiscounts'}</a>
                <a href="#" class="list-group-item" > {l s='Active Students accounts' mod='studentdiscounts'}</a>
            </div>
        </div>
         {* list your admin tpl *}
        <div id="setting" class="col-lg-10 psgdpr_menu addons-hide">
            {$settingStudentDiscount}
        </div>

        <div id="studentDomains" class="col-lg-10 psgdpr_menu addons-hide" style="display: none">
            {$studentDomains}
        </div>

        <div id="studentEmailVerification" class="col-lg-10 psgdpr_menu addons-hide" style="display: none">
            {$studentEmailVerification}
        </div>

        <div id="customerActivity" class="col-lg-10 psgdpr_menu addons-hide" style="display: none">

        </div>

        <div id="faq" class="col-lg-10 psgdpr_menu addons-hide" style="display: none">

        </div>
    </div>
</div>

<script>
    function changeSection(section) {
        switch (section) {
            case 'setting': {
                $("#setting").css("display", "block");
                $("#studentDomains").css("display", "none");
                $("#studentEmailVerification").css("display", "none");
            } break;
            case 'studentDomains': {
                $("#studentEmailVerification").css("display", "none");
                $("#studentDomains").css("display", "block");
                $("#setting").css("display", "none");
            } break;
            case 'studentEmailVerification': {
                $("#studentEmailVerification").css("display", "block");
                $("#studentDomains").css("display", "none");
                $("#setting").css("display", "none");
            }
        }
    }
</script>

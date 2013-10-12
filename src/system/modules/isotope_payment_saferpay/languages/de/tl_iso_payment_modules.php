<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  IMS Internet Marketing Solutions Ltd. 2013
 * @author     Dominik Zogg <dz@erfolgreiche-internetseiten.ch>
 * @package    isotope_payment_saferpay
 * @license    LGPLv3
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payment_saferpay_accountid']					= array('Saferpay Account-ID', 'Bitte geben Sie Ihre eindeutige Saferpay Account-ID ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payment_saferpay_password']					= array('Saferpay Password', 'Bitte geben Sie Ihr Saferpay Passwort ein. Ohne ist es nicht möglich fehlgeschlagene Zahlungen, korrekt abzubrechen.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payment_saferpay_description']				= array('Saferpay Bestell-Beschreibung', 'Diese Beschreibung wird dem Kunden im Saferpay-Bestellprozess angezeigt.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payment_saferpay_paymentmethods']			= array('Saferpay Zahlungart', 'Übergeben Sie Saferpay, welche Zahlungsarten sie nutzen wollen. Wählen Sie nur jene, welche in ihrem Saferpaykonto freigeschaltet sind.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payment_saferpay_billpay']					= array('Saferpay Billpay Integration', 'Hier können Sie die Billpay Integration aktivieren.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payment_saferpay_paymentmethods_billpay']	= array('Saferpay Billpay Zahlungart', 'Übergeben Sie Saferpay, welche Zahlungsarten (Billpay) sie nutzen wollen. Wählen Sie nur jene, welche in ihrem Saferpaykonto freigeschaltet sind.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billpay_legend'] = 'Billpay';
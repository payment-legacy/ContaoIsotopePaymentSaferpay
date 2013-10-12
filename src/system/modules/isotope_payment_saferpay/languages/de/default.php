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

use Payment\Saferpay\Data\PayInitParameterInterface;
use Payment\Saferpay\Data\Billpay\BillpayPayInitParameterInterface;

/**
 * Payment modules
 */
$GLOBALS['ISO_LANG']['PAY']['payment_saferpay'] = array('Saferpay (payment/saferpay)', '');

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['pay_with_saferpay'] = array(
	'Bezahlen mit Saferpay (payment/saferpay)',
	'Sie werden nun an Saferpay zur bezahlung Ihrere Bestellung weitergeleitet. Wenn Sie nicht sofort weitergeleitet werden, klicken Sie bitte auf "Jetzt bezahlen".',
	'Jetzt bezahlen'
);

$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_MASTERCARD] = 'MasterCard';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_VISA] = 'Visa';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_AMERICAN_EXPRESS] = 'American Express';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_DINERSCLUB] = 'Dinersclub';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_JCB] = 'JCB Kreditkarte';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_SAFERPAY_TESTCARD] = 'Saferpay Testkarte';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_LASER_CARD] = 'Laser Card';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_BONUS_CARD] = 'BONUS CARD';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_POSTFINANCE_E_FINANCE] = 'PostFinance E-Banking';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_POSTFINANCE_CARD] = 'PostFinance Karte';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_MAESTRO_INTERNATIONAL] = 'Maestro karte';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_MYONE] = 'myOne';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_DIRECTDEBIT] = 'Lastschrift';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_INVOICE] = 'Rechnung';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_IMMEDIATE_TRANSFER] = 'Sofortüberweisung';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_PAYPAL] = 'Paypal';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_GIROPAY] = 'Giropay';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_IDEAL] = 'iDEAL';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_CLICK_N_BUY] = 'Click\'n\'Buy';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_HOMEBANKING_AT] = 'Homebanking Austria';
$GLOBALS['TL_LANG']['MSC'][PayInitParameterInterface::PAYMENTMETHOD_MPASS] = 'mpass';
$GLOBALS['TL_LANG']['MSC'][BillpayPayInitParameterInterface::PAYMENTMETHOD_BILLPAY_LSV] = 'Billpay Lastschrift';
$GLOBALS['TL_LANG']['MSC'][BillpayPayInitParameterInterface::PAYMENTMETHOD_BILLPAY_INVOICE] = 'Billpay Rechnung';

$GLOBALS['TL_LANG']['MSC'][BillpayPayInitParameterInterface::LEGALFORM_GMBH] = 'GmbH';
$GLOBALS['TL_LANG']['MSC'][BillpayPayInitParameterInterface::LEGALFORM_AG] = 'AG';
$GLOBALS['TL_LANG']['MSC'][BillpayPayInitParameterInterface::LEGALFORM_MISC] = 'andere Firmenform';
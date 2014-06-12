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
 * @author	 Dominik Zogg <dz@erfolgreiche-internetseiten.ch>
 * @package	isotope_payment_saferpay
 * @license	LGPLv3
 */

use Payment\Saferpay\Data\PayInitParameter;

class IsotopePaymentSaferpay extends AbstractIsotopePaymentSaferpay
{
	/**
	 * @return string
	 */
	public function checkoutForm()
	{
		$objPayInitParameter = new PayInitParameter;
		$objPayInitParameter
			->setAmount(round($this->getCart()->grandTotal * 100, 0))
			->setCurrency($this->getConfig()->currency)
			->setAccountid($this->payment_saferpay_accountid)
			->setDescription(urlencode($this->payment_saferpay_description))
			->setOrderid($this->getOrder()->id)
			->setSuccesslink($this->Environment->base . $this->addToUrl('step=complete', true))
			->setFaillink($this->Environment->base . $this->addToUrl('step=failed', true))
			->setBacklink($this->Environment->base . $this->addToUrl('step=failed', true))
			->setPaymentmethods(unserialize($this->payment_saferpay_paymentmethods))
		;

		if($salutation = $this->getBillingAddress()->salutation) {
			$objPayInitParameter->setGender($this->getGender($salutation));
		}

		if($firstname = $this->getBillingAddress()->firstname) {
			$objPayInitParameter->setFirstname($firstname);
		}

		if($lastname = $this->getBillingAddress()->lastname) {
			$objPayInitParameter->setLastname($lastname);
		}

		if($street = $this->getBillingAddress()->street_1) {
			$objPayInitParameter->setStreet($street);
		}

		if($postal = $this->getBillingAddress()->postal) {
			$objPayInitParameter->setZip($postal);
		}

		if($city = $this->getBillingAddress()->city) {
			$objPayInitParameter->setCity($city);
		}

		if($country = $this->getBillingAddress()->country) {
			$objPayInitParameter->setCountry(strtoupper($country));
			$objPayInitParameter->setLangid(strtoupper($country));
		}

		if($phone = $this->getBillingAddress()->phone) {
			$objPayInitParameter->setPhone($phone);
		}

		if($email = $this->getBillingAddress()->email) {
			$objPayInitParameter->setEmail($email);
		}

		$strUrl = $this->getSaferpay()->createPayInit($objPayInitParameter);

		if(!$strUrl)
		{
			$this->log('Payment not successfull', 'PaymentSaferpay checkoutForm()', TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}

		$this->redirect($strUrl);
	}

	/**
	 * @return bool
	 */
	public function processPayment()
	{
		$payConfirmParameter = $this->getSaferpay()->verifyPayConfirm($_GET['DATA'], $this->Input->get('SIGNATURE'));
		if($payConfirmParameter->get('AMOUNT') == round($this->getCart()->grandTotal * 100, 0) &&
		   $payConfirmParameter->get('CURRENCY') == $this->getConfig()->currency) {
			$this->getSaferpay()->payCompleteV2($payConfirmParameter, 'Settlement', $this->payment_saferpay_password);
			$this->getOrder()->date_paid = time();
			$this->getOrder()->save();
			$this->getOrder()->updateOrderStatus($this->new_order_status);
			return true;
		} else {
			$this->getSaferpay()->payCompleteV2($payConfirmParameter, 'Cancel', $this->payment_saferpay_password);
			$this->log('Payment not successfull', 'PaymentSaferpay processPayment()', TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}
	}
}
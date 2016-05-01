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
use Payment\Saferpay\Data\PayConfirmParameter;
use Payment\Saferpay\Data\PayCompleteParameter;
use Payment\Saferpay\Data\PayCompleteResponse;
use Payment\Saferpay\Data\Collection\Collection;
use Payment\Saferpay\Data\Billpay\BillpayPayInitParameterInterface;
use Payment\Saferpay\Data\Billpay\BillpayPayInitParameter;
use Payment\Saferpay\Data\Billpay\BillpayPayConfirmParameter;
use Payment\Saferpay\Data\Billpay\BillpayPayCompleteParameter;
use Payment\Saferpay\Data\Billpay\BillpayPayCompleteResponse;

class IsotopePaymentSaferpayBillpay extends AbstractIsotopePaymentSaferpay
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
			->setProviderset(unserialize($this->payment_saferpay_providerset_billpay))
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

		$objBillpayPayInitParameter = new BillpayPayInitParameter;

		if($addressAddition = $this->getBillingAddress()->street_2) {
			$objBillpayPayInitParameter->setAddressAddition($addressAddition);
		}

		if($deliverySalutation = $this->getShippingAdress()->salutation) {
			$objBillpayPayInitParameter->setDeliveryGender($this->getGender($deliverySalutation));
		}

		if($deliveryFirstname = $this->getShippingAdress()->firstname) {
			$objBillpayPayInitParameter->setDeliveryFirstname($deliveryFirstname);
		}

		if($deliveryLastname = $this->getShippingAdress()->lastname) {
			$objBillpayPayInitParameter->setDeliveryLastname($deliveryLastname);
		}

		if($deliveryStreet = $this->getShippingAdress()->street_1) {
			$objBillpayPayInitParameter->setDeliveryStreet($deliveryStreet);
		}

		if($deliveryAddressAddition = $this->getShippingAdress()->street_2) {
			$objBillpayPayInitParameter->setDeliveryAddressAddition($deliveryAddressAddition);
		}

		if($deliveryZip = $this->getShippingAdress()->postal) {
			$objBillpayPayInitParameter->setDeliveryZip($deliveryZip);
		}

		if($deliveryCity = $this->getShippingAdress()->city) {
			$objBillpayPayInitParameter->setDeliveryCity($deliveryCity);
		}

		if($deliveryCountry = $this->getShippingAdress()->country) {
			$objBillpayPayInitParameter->setDeliveryCountry(strtoupper($deliveryCountry));
		}

		if($deliveryPhone = $this->getShippingAdress()->phone) {
			$objBillpayPayInitParameter->setDeliveryPhone($deliveryPhone);
		}

		if($this->getGender($this->getBillingAddress()->salutation) == 'c')
		{
			$objBillpayPayInitParameter->setLegalform(BillpayPayInitParameterInterface::LEGALFORM_MISC);
		}

		$objPayInitParameterCollection = new Collection($objPayInitParameter->getRequestUrl());
		$objPayInitParameterCollection->addCollectionItem($objPayInitParameter);
		$objPayInitParameterCollection->addCollectionItem($objBillpayPayInitParameter);

		$strUrl = $this->getSaferpay()->createPayInit($objPayInitParameterCollection);

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

		$objPayConfirmParameter = new PayConfirmParameter;
		$objBillpayPayConfirmParameter = new BillpayPayConfirmParameter;

		$objPayConfirmParameterCollection = new Collection($objPayConfirmParameter->getRequestUrl());
		$objPayConfirmParameterCollection->addCollectionItem($objPayConfirmParameter);
		$objPayConfirmParameterCollection->addCollectionItem($objBillpayPayConfirmParameter);

		$objPayConfirmParameterCollection = $this->getSaferpay()->verifyPayConfirm(
			$_REQUEST['DATA'],
			$this->Input->get('SIGNATURE'),
			$objPayConfirmParameterCollection
		);

		$objPayCompleteParameter = new PayCompleteParameter;
		$objBillpayPayCompleteParameter = new BillpayPayCompleteParameter;

		$objPayCompleteParameterCollection = new Collection($objPayCompleteParameter->getRequestUrl());
		$objPayCompleteParameterCollection->addCollectionItem($objPayCompleteParameter);
		$objPayCompleteParameterCollection->addCollectionItem($objBillpayPayCompleteParameter);

		$objPayCompleteResponse = new PayCompleteResponse;
		$objBillpayPayCompleteResponse = new BillpayPayCompleteResponse;

		$objPayCompleteResponseCollection = new Collection($objPayCompleteResponse->getRequestUrl());
		$objPayCompleteResponseCollection->addCollectionItem($objPayCompleteResponse);
		$objPayCompleteResponseCollection->addCollectionItem($objBillpayPayCompleteResponse);

		if($objPayConfirmParameterCollection->get('AMOUNT') == round($this->getCart()->grandTotal * 100, 0) &&
		   $objPayConfirmParameterCollection->get('CURRENCY') == $this->getConfig()->currency)
		{
			$this->getSaferpay()->payCompleteV2(
				$objPayConfirmParameterCollection,
				'Settlement',
				$this->payment_saferpay_password,
				$objPayCompleteParameterCollection,
				$objPayCompleteResponseCollection
			);

			$this->getOrder()->pob_duedate = new \DateTime($objPayCompleteResponseCollection->get('POB_DUEDATE'));
			$this->getOrder()->pob_accountholder = $objPayCompleteResponseCollection->get('POB_ACCOUNTHOLDER');
			$this->getOrder()->pob_accountnumber = $objPayCompleteResponseCollection->get('POB_ACCOUNTNUMBER');
			$this->getOrder()->pob_bankcode = $objPayCompleteResponseCollection->get('POB_BANKCODE');
			$this->getOrder()->pob_bankname = $objPayCompleteResponseCollection->get('POB_BANKNAME');
			$this->getOrder()->pob_payernote = $objPayCompleteResponseCollection->get('POB_PAYERNOTE');
			$this->getOrder()->save();
			$this->getOrder()->updateOrderStatus($this->new_order_status);
			return true;
		} else {
			$this->getSaferpay()->payCompleteV2(
				 $objPayConfirmParameterCollection,
				'Cancel',
				$this->payment_saferpay_password,
				$objPayCompleteParameterCollection,
				$objPayCompleteResponseCollection
			);
			$this->log('Payment not successfull', 'PaymentSaferpay processPayment()', TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}
	}
}

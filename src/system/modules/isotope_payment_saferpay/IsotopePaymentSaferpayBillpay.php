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
			->setGender($this->getGender($this->getBillingAddress()->salutation))
			->setFirstname($this->getBillingAddress()->firstname)
			->setLastname($this->getBillingAddress()->lastname)
			->setStreet($this->getBillingAddress()->street_1)
			->setZip($this->getBillingAddress()->postal)
			->setCity($this->getBillingAddress()->city)
			->setCountry(strtoupper($this->getBillingAddress()->country))
			->setLangid(strtoupper($this->getBillingAddress()->country))
			->setPhone($this->getBillingAddress()->phone)
			->setEmail($this->getBillingAddress()->email)
		;

		$objBillpayPayInitParameter = new BillpayPayInitParameter;
		$objBillpayPayInitParameter
			->setAddressAddition(!is_null($this->getBillingAddress()->street_2) ? $this->getBillingAddress()->street_2 : '')
			->setDeliveryGender($this->getGender($this->getShippingAdress()->salutation))
			->setDeliveryFirstname($this->getShippingAdress()->firstname)
			->setDeliveryLastname($this->getShippingAdress()->lastname)
			->setDeliveryStreet($this->getShippingAdress()->street_1)
			->setDeliveryAddressAddition(!is_null($this->getShippingAdress()->street_2) ? $this->getShippingAdress()->street_2 : '')
			->setDeliveryZip($this->getShippingAdress()->postal)
			->setDeliveryCity($this->getShippingAdress()->city)
			->setDeliveryCountry(strtoupper($this->getShippingAdress()->country))
			->setDeliveryPhone($this->getShippingAdress()->phone)
		;

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

			$this->getOrder()->pob_duedate = $objPayCompleteResponseCollection->get('POB_DUEDATE');
			$this->getOrder()->pob_accountholder = $objPayCompleteResponseCollection->get('POB_ACCOUNTHOLDER');
			$this->getOrder()->pob_accountnumber = $objPayCompleteResponseCollection->get('POB_ACCOUNTNUMBER');
			$this->getOrder()->pob_bankcode = $objPayCompleteResponseCollection->get('POB_BANKCODE');
			$this->getOrder()->pob_bankname = $objPayCompleteResponseCollection->get('POB_BANKNAME');
			$this->getOrder()->pob_payernote = $objPayCompleteResponseCollection->get('POB_PAYERNOTE');
			$this->getOrder()->save();
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
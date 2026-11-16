<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityMerchantPortalGui\Communication\Form;

use Codeception\Test\Unit;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Form\MerchantLoginForm;
use Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiDependencyProvider;
use Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\MerchantPortalLoginFormExpanderPluginInterface;
use SprykerTest\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiCommunicationTester;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityMerchantPortalGui
 * @group Communication
 * @group Form
 * @group MerchantLoginFormTest
 * Add your own group annotations below this line
 */
class MerchantLoginFormTest extends Unit
{
    protected const string FIELD_CAPTCHA = 'captcha';

    protected SecurityMerchantPortalGuiCommunicationTester $tester;

    public function testBuildFormAddsFieldOfMerchantPortalLoginFormExpanderPluginBeforeLoginButton(): void
    {
        // Arrange
        $this->tester->setDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_MERCHANT_PORTAL_LOGIN_FORM_EXPANDER, [
            $this->createCaptchaFieldExpanderPlugin(),
        ]);

        // Act
        $form = $this->createMerchantLoginForm();

        // Assert
        $this->assertSame(
            [MerchantLoginForm::FIELD_USERNAME, MerchantLoginForm::FIELD_PASSWORD, static::FIELD_CAPTCHA, MerchantLoginForm::BUTTON_LOGIN],
            array_keys($form->all()),
        );
    }

    public function testBuildFormKeepsLoginFieldsWhenNoMerchantPortalLoginFormExpanderPluginIsRegistered(): void
    {
        // Act
        $form = $this->createMerchantLoginForm();

        // Assert
        $this->assertSame(
            [MerchantLoginForm::FIELD_USERNAME, MerchantLoginForm::FIELD_PASSWORD, MerchantLoginForm::BUTTON_LOGIN],
            array_keys($form->all()),
        );
    }

    /**
     * The Zed form type has no factory setter, so `getFactory()` is replaced to hand over the factory
     * built from the module dependency provider with the test dependencies applied.
     *
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    protected function createMerchantLoginForm(): FormInterface
    {
        $merchantLoginFormMock = $this->getMockBuilder(MerchantLoginForm::class)
            ->onlyMethods(['getFactory'])
            ->getMock();
        $merchantLoginFormMock->expects($this->atLeastOnce())
            ->method('getFactory')
            ->willReturn($this->tester->getFactory());

        return Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addType($merchantLoginFormMock)
            ->getFormFactory()
            ->create($merchantLoginFormMock::class);
    }

    protected function createCaptchaFieldExpanderPlugin(): MerchantPortalLoginFormExpanderPluginInterface
    {
        $merchantPortalLoginFormExpanderPluginMock = $this->createMock(MerchantPortalLoginFormExpanderPluginInterface::class);
        $merchantPortalLoginFormExpanderPluginMock->expects($this->once())
            ->method('expand')
            ->willReturnCallback(function (FormBuilderInterface $builder): FormBuilderInterface {
                return $builder->add(static::FIELD_CAPTCHA, TextType::class);
            });

        return $merchantPortalLoginFormExpanderPluginMock;
    }
}

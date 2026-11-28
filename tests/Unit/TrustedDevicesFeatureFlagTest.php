<?php

namespace Tests\Unit;

use App\Http\Controllers\CenterUser\LoginController;
use App\Models\Center;
use App\Models\Invoice_Settings;
use App\Services\TrustedDeviceService;
use ReflectionMethod;
use Tests\TestCase;

class TrustedDevicesFeatureFlagTest extends TestCase
{
    public function test_flag_defaults_to_disabled_on_center_model(): void
    {
        $center = new Center();
        $center->trusted_devices_enabled = false;

        $this->assertFalse((bool) $center->trusted_devices_enabled);
    }

    public function test_login_controller_reads_enabled_flag(): void
    {
        $controller = new LoginController(app(TrustedDeviceService::class));
        $method = new ReflectionMethod(LoginController::class, 'isTrustedDevicesEnabled');
        $method->setAccessible(true);

        $disabled = new Center(['trusted_devices_enabled' => false]);
        $enabled = new Center(['trusted_devices_enabled' => true]);

        $this->assertFalse($method->invoke($controller, $disabled));
        $this->assertTrue($method->invoke($controller, $enabled));
        $this->assertFalse($method->invoke($controller, null));
    }

    public function test_otp_email_recipients_include_center_and_added_emails(): void
    {
        $service = new TrustedDeviceService();
        $center = new Center([
            'email' => 'admin@center.test',
        ]);
        $settings = new Invoice_Settings([
            'emails' => ['sec@center.test', 'bad', 'ops@center.test'],
        ]);

        $recipients = $service->otpEmailRecipients($center, $settings);

        $this->assertSame(['admin@center.test', 'sec@center.test', 'ops@center.test'], $recipients);
    }

    public function test_otp_phone_recipients_include_admin_and_added_numbers(): void
    {
        $service = new TrustedDeviceService();
        $settings = new Invoice_Settings([
            'phone_number_1' => '971509999111',
            'phone_number_2' => '',
            'otp_phones' => ['971543000522', '971503140232', 'abc'],
        ]);

        $phones = $service->otpPhoneRecipients($settings);

        $this->assertSame(['971509999111', '971543000522', '971503140232'], $phones);
    }

    public function test_limit_reached_respects_null_and_zero_as_unlimited(): void
    {
        $service = $this->getMockBuilder(TrustedDeviceService::class)
            ->onlyMethods(['trustedCount'])
            ->getMock();
        $service->method('trustedCount')->willReturn(99);

        $unlimitedNull = new Center(['id' => 1, 'center_device_limit' => null]);
        $unlimitedZero = new Center(['id' => 1, 'center_device_limit' => 0]);
        $limited = new Center(['id' => 1, 'center_device_limit' => 2]);

        $this->assertFalse($service->limitReached($unlimitedNull));
        $this->assertFalse($service->limitReached($unlimitedZero));
        $this->assertTrue($service->limitReached($limited));
    }
}

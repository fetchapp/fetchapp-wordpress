<?php declare(strict_types=1);

namespace FetchApp\Tests;

use FetchApp\API\FetchApp;
use FetchApp\API\AccountDetail;

final class AccountTest extends FetchAppBaseTest
{
    public function testClass(): void
    {
        $fetch = self::$fetch;

        $account = $fetch->getAccountDetails();//    That was easy!

        $this->assertInstanceOf(
            AccountDetail::class,
            $account
        );
    }

    public function testFields(): void
    {
        $fetch = self::$fetch;

        $account = $fetch->getAccountDetails();//    That was easy!

        $this->assertInstanceOf(
            AccountDetail::class,
            $account
        );

        $this->assertSame((int)$_ENV['TEST_ACCOUNT_ID'], $account->getAccountID());
        $this->assertSame($_ENV['TEST_ACCOUNT_NAME'], $account->getAccountName());
        $this->assertSame($_ENV['TEST_ACCOUNT_BILLING_EMAIL'], $account->getBillingEmail());
        $this->assertSame($_ENV['TEST_ACCOUNT_EMAIL_ADDRESS'], $account->getEmailAddress());
        $this->assertSame($_ENV['TEST_ACCOUNT_URL'], $account->getURL());
        $this->assertNull($account->getItemDownloadLimit());
        $this->assertNull($account->getOrderExpirationInHours());
    }
}

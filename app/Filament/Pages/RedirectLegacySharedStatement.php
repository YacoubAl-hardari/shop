<?php

namespace App\Filament\Pages;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Models\MerchantCustomerStatementShare;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * إعادة توجيه الروابط القديمة (إشعارات / روابط سابقة) إلى رابط UUID الجديد.
 */
class RedirectLegacySharedStatement extends Page
{
    use HasRoleAccess;

    protected static function allowedRoles(): array
    {
        return [UserType::USER, UserType::ADMIN];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $title = 'كشف الحساب المشترك';

    protected static ?string $slug = 'view-shared-customer-statement';

    protected string $view = 'filament.pages.view-shared-customer-statement-redirect';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $token = request()->query('token');

        if (is_string($token) && $token !== '') {
            $this->redirect(ViewSharedCustomerStatement::statementUrl($token));

            return;
        }

        $shareId = (int) request()->query(
            'vsid',
            request()->query('share_id', request()->query('share', 0)),
        );

        if ($shareId <= 0) {
            $segments = request()->segments();
            $lastSegment = end($segments);

            if (is_numeric($lastSegment)) {
                $shareId = (int) $lastSegment;
            }
        }

        if ($shareId > 0) {
            $share = MerchantCustomerStatementShare::query()->find($shareId);

            if ($share?->uuid) {
                $this->redirect(ViewSharedCustomerStatement::statementUrl($share->uuid));

                return;
            }
        }

        $this->redirect(SharedCustomerStatements::getUrl());
    }
}

<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'تسجيل حساب جديد'; // Register new team in Arabic
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الحساب') // Account name
                    ->required()
                    ->maxLength(30)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                
                TextInput::make('slug')
                    ->label('المعرف الفريد') // Unique identifier
                    ->required()
                    ->maxLength(30)
                    ->unique(Team::class, 'slug')
                    ->helperText('رابط حسابك سيكون: /admin/{slug}')
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->validationMessages([
                        'regex' => 'يجب أن يحتوي المعرف على حروف صغيرة وأرقام وشرطات فقط.',
                    ]),
                
                Textarea::make('description')
                    ->label('الوصف') // Description
                    ->maxLength(50)
                    ->columnSpanFull(),
            ]);
    }

    public function mount(): void
    {
        // التحقق من أن المستخدم ليس لديه حساب بالفعل
        if (Auth::user()->teams()->exists()) {
            // إعادة توجيه للحساب الموجود
            $team = Auth::user()->teams()->first();
            redirect()->to('/admin/' . $team->slug);
        }
    }

    protected function handleRegistration(array $data): Team
    {
        // التحقق مرة أخرى قبل الإنشاء
        if (Auth::user()->teams()->exists()) {
            $this->halt();
            return Auth::user()->teams()->first();
        }

        $team = Team::create($data);

        // Attach the current user as the owner
        $team->members()->attach(Auth::user(), ['role' => 'owner']);

        return $team;
    }
}


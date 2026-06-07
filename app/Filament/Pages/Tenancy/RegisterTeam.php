<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Database\Seeders\ChartOfAccountsSeeder;
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
        return 'تسجيل فرع جديد';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الفرع')
                    ->required()
                    ->maxLength(30)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->label('المعرف الفريد')
                    ->required()
                    ->maxLength(30)
                    ->unique(Team::class, 'slug')
                    ->helperText('رابط فرعك سيكون: /admin/{slug}')
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->validationMessages([
                        'regex' => 'يجب أن يحتوي المعرف على حروف صغيرة وأرقام وشرطات فقط.',
                    ]),

                Textarea::make('description')
                    ->label('الوصف')
                    ->maxLength(50)
                    ->columnSpanFull(),
            ]);
    }

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->isUser() && $user->teams()->exists()) {
            $team = $user->teams()->first();
            redirect()->to('/admin/'.$team->slug);
        }
    }

    protected function handleRegistration(array $data): Team
    {
        $user = Auth::user();

        if ($user->isUser() && $user->teams()->exists()) {
            $this->halt();

            return $user->teams()->first();
        }

        $team = Team::create($data);

        $team->members()->attach($user, ['role' => 'owner']);

        (new ChartOfAccountsSeeder)->run($team);

        return $team;
    }
}

<?php

namespace Database\Seeders\Users;

use App\Domain\Users\Actions\CreateUser;
use App\Enums\UserTypesEnum;

class CapeAndBayUserSeeder extends UserSeeder
{
    protected function getUsersToAdd(): array
    {
        return [
            [
                'first_name' => 'Parth',
                'last_name' => 'Jasani',
                'email' => 'parth.jasani@bacancy.com',
            ],
            [
                'first_name' => 'Pravin',
                'last_name' => 'Kumar',
                'email' => 'pravin.kumar@bacancy.com',
            ],
            [
                'first_name' => 'Tareq',
                'last_name' => 'Othman',
                'email' => 'tareq@capeandbay.com',
            ],
            [
                'first_name' => 'Zaid',
                'last_name' => 'Dabus',
                'email' => 'zaid@capeandbay.com',
            ],
            [
                'first_name' => 'Brett',
                'last_name' => 'Milam',
                'email' => 'brett@capeandbay.com',
            ],
            [
                'first_name' => 'Ammar',
                'last_name' => 'Zalatino',
                'email' => 'ammar@capeandbay.com',
            ],
            [
                'first_name' => 'Philip',
                'last_name' => 'Krogel',
                'email' => 'philip@capeandbay.com',
            ],
            [
                'first_name' => 'Sterling',
                'last_name' => 'Webb',
                'email' => 'sterling@capeandbay.com',
            ],
            [
                'first_name' => 'Shivam',
                'last_name' => 'Shewa',
                'email' => 'shivam@capeandbay.com',
            ],
            [
                'first_name' => 'Alec',
                'last_name' => 'Salzman',
                'email' => 'alec@gymrevenue.com',
            ],
            [
                'first_name' => 'Sami',
                'last_name' => 'Carroll',
                'email' => 'sami@gymrevenue.com',
            ],
            [
                'first_name' => 'Ron',
                'last_name' => 'Merryman',
                'email' => 'ron@gymrevenue.com',
            ],
            [
                'first_name' => 'Tommy',
                'last_name' => 'Lee',
                'email' => 'tommy@capeandbay.com',
            ],
            [
                'first_name' => 'Blair',
                'last_name' => 'Patterson',
                'email' => 'blair@capeandbay.com',
            ],
            [
                'first_name' => 'Adam',
                'last_name' => 'Vandall',
                'email' => 'adam@capeandbay.com',
            ],
            [
                'first_name' => 'Chris',
                'last_name' => 'Italiano',
                'email' => 'chris@capeandbay.com',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Duncan',
                'email' => 'david@capeandbay.com',
            ],
            [
                'first_name' => 'Larry',
                'last_name' => 'Herrod',
                'email' => 'larry@capeandbay.com',
            ],
            [
                'first_name' => 'Jen',
                'last_name' => 'Jen',
                'email' => 'jen@gymrevenue.com',
            ],
            [
                'first_name' => 'Chrys',
                'last_name' => 'Ugwu',
                'email' => 'chrys@capeandbay.com',
            ],
            [
                'first_name' => 'Abdur',
                'last_name' => 'Rauf',
                'email' => 'abdur@capeandbay.com',
            ],
            [
                'first_name' => 'Mobashir',
                'last_name' => 'Monim',
                'email' => 'mmonim@gymrevenue.com',
            ],
            [
                'first_name' => 'Sovon',
                'last_name' => 'Saha',
                'email' => 'ssaha@gymrevenue.com',
            ],
            [
                'first_name' => 'Chandni',
                'last_name' => 'Soni',
                'email' => 'chandni.soni@bacancy.com',
            ],
            [
                'first_name' => 'Jayushi',
                'last_name' => 'Jain',
                'email' => 'jayushi.jain@bacancy.com',
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Khan',
                'email' => 'skhan@gymrevenue.com',
            ],
            [
                'first_name' => 'Clayton',
                'last_name' => 'Krogel',
                'email' => 'clayton@capeandbay.com',
            ],
        ];
    }

    public static function addUser(array $user, string $team_id)
    {
        CreateUser::run([
            'client_id' => null,
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'password_hashed' => '$2y$10$S9YUvKwjvgTuj8K5wXlqTOBUDJ0hOEk/dhBoVAm/vYVchKS/taQt2', // Hello123!
            'team_id' => $team_id,
            'role_id' => 1,
            //            'home_location_id' => $home_location_id,
            'manager' => 'Senior Manager',
            'user_type' => UserTypesEnum::EMPLOYEE,
        ]);
    }
}

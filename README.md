<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Websites:

https://www.w3schools.com/php/func_date_date.asp

Commands:
php artisan serve --port=8000
php artisan install:api //to get api routes
php artisan route:list
php artisan route:clear
php artisan make:migration add-history-column-user --table=users
php artisan make:model Setting -m // to create table and matching class
php artisan migrate //to send local data to db

//Search
Model::where('data->name', 'LIKE', '%wat%')->get();
//sorting
->orderBy('column_name->json_key')
->orderByRaw('CAST(JSON_EXTRACT(json_column, "$.json_key") AS unsigned)', 'asc') //correct one
//select json column
 return DB::table('users')
                ->select(
                    'email as id',
                    DB::raw('json_extract(user_details, "$.signup_data.name") as name'),
DB::raw('json_extract(user_details, "$.signup_data.email") as email'),
                    DB::raw('json_extract(user_details, "$.signup_data.role") as role'),
DB::raw('json_extract(user_details, "$.signup_data.countryCode") as countryCode'),
                    DB::raw('json_extract(user_details, "$.signup_data.phoneNumber") as phoneNumber'),
DB::raw('json_extract(user_details, "$.signup_data.investment") as investment'),
                    DB::raw('json_extract(user_details, "$.signup_data.whatsappUpdate") as whatsappUpdate'),
'created_at',
'updated_at'
)
->where($conditions)->orderByDesc('created_at')->paginate($pagingParams[config('app-constants.pagingKeys.pageSize')],
['*'],'users',$pagingParams[config('app-constants.pagingKeys.pageIndex')]);

update users set user_details = JSON_SET(user_details, "$.signup_data.status", "Active");

UPDATE users SET user_details = JSON_MERGE(user_details, '{"status":"Active"}');

update users set user_details = JSON_SET(user_details, "$.signup_data.role", "Active") WHERE JSON_EXTRACT(user_details, '$.signup_data.role')='SCHEME_MUMBER';

select \* from users WHERE JSON_EXTRACT(user_details, '$.signup_data.role')='SCHEME_MUMBER';

select \* from users WHERE JSON_EXTRACT(user_details, '$.signup_data.role')='SCHEME_MUMBER';

SELECT email FROM users ORDER BY RAND() LIMIT 20;

//update referral code
update users set user_details = JSON_SET(user_details, "$.signup_data.referralCode", 17342053862127131) WHERE email IN(select email from users where email!=17342053862127131 and JSON_EXTRACT(user_details, '$.signup_data.referralCode')!=17342053862127131 order by rand() limit 2);

select email from users where email!=17342053862127131 and JSON_EXTRACT(user_details, '$.signup_data.referralCode')!=17342053862127131 and JSON_EXTRACT(user_details, '$.signup_data.role')='SCHEME_MUMBER' order by rand() limit 20;

//get only schemembers randomly
select email from users WHERE JSON_EXTRACT(user_details, '$.signup_data.role')='SCHEME_MEMBER' order by rand() limit 10;
17342057631538516,
17342054635359142,
17342055949615265,
17342054727020945,
17342053868351203,
17342068122386148,
17342067114076531,
17342056857364768,
17342055155972515,
17342056407139774

//clear referral codes
update users set user_details = JSON_SET(user_details, "$.signup_data.referralCode", '');

//update referral code in json
update users set user_details = JSON_SET(user_details, "$.signup_data.referralCode", 17342057631538516) WHERE email IN(17342057631538516,
17342054635359142,
17342055949615265,
17342054727020945,
17342053868351203,
17342068122386148,
17342067114076531,
17342056857364768,
17342055155972515,
17342056407139774);

//Get referrals of a member

//Get referrals paid
select \* from payments where userId IN(select email from users where JSON_EXTRACT(user_details, '$.signup_data.referralCode')=17342057631538516);



SELECT * FROM payments WHERE created_at + INTERVAL 1500 DAY <= NOW();
SELECT * FROM payments WHERE created_at + INTERVAL 1500 DAY <= '2024-12-05';

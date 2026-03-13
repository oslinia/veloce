<?php

namespace Application\Controller;

use Veloce\Routing\Rule;

Rule::route('/', 'main', Main::class);

Rule::route('/redirect', 'main.redirect', Main::class);

Rule::route('/user/slug:{name}-id:{id}.and{ext}', 'user', User::class)
    ->where(id: '\d{1,3}');

Rule::route('/user/update', 'user.update', User::class);

Rule::route('/archive', 'archive', Archive::class);

Rule::route('/archive/{year}', 'archive.select', Archive::class)
    ->where(year: '\d{4}');
Rule::route('/archive/{year}/{month}', 'archive.select', Archive::class)
    ->where(year: '\d{4}', month: '\d{2}');
Rule::route('/archive/{year}/{month}/{day}', 'archive.select', Archive::class)
    ->where(year: '\d{4}', month: '\d{2}', day: '\d{2}');

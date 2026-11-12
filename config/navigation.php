<?php

return [

    'owner' => [
        ['label' => 'Overview', 'route' => 'owner.dashboard', 'active' => ['owner.dashboard'], 'icon' => 'layout-dashboard', 'tab' => true],
        ['label' => 'My Pets', 'route' => 'owner.pets.index', 'active' => ['owner.pets.*'], 'icon' => 'paw-print', 'tab' => true],
        ['label' => 'Appointments', 'route' => 'owner.appointments.index', 'active' => ['owner.appointments.*'], 'icon' => 'calendar', 'badge' => 'appointments', 'tab' => true],
        ['label' => 'Shop', 'route' => 'products.index', 'active' => ['products.*'], 'icon' => 'shopping-bag'],
        ['label' => 'Cart', 'route' => 'owner.cart.index', 'active' => ['owner.cart.index', 'owner.cart.add', 'owner.cart.update', 'owner.cart.remove', 'owner.cart.items.show'], 'icon' => 'shopping-cart', 'badge' => 'cart', 'tab' => true],
        ['label' => 'Orders', 'route' => 'owner.cart.orders', 'active' => ['owner.cart.orders', 'owner.checkout.*', 'owner.orders.*'], 'icon' => 'receipt'],
        ['label' => 'Adopt', 'route' => 'adopt.index', 'active' => ['adopt.*'], 'icon' => 'heart'],
        ['label' => 'Vets', 'route' => 'vets.index', 'active' => ['vets.*'], 'icon' => 'stethoscope'],
        ['label' => 'Care Tips', 'route' => 'care.index', 'active' => ['care.*'], 'icon' => 'book-open'],
        ['label' => 'Alerts', 'route' => 'notifications.index', 'active' => ['notifications.*'], 'icon' => 'bell', 'badge' => 'alerts'],
    ],

    'vet' => [
        ['label' => 'Overview', 'route' => 'vet.dashboard', 'active' => ['vet.dashboard'], 'icon' => 'layout-dashboard', 'tab' => true],
        ['label' => 'Appointments', 'route' => 'vet.appointments', 'active' => ['vet.appointments', 'vet.appointments.update', 'vet.pets.history'], 'icon' => 'calendar', 'badge' => 'appointments', 'tab' => true],
        ['label' => 'My Profile', 'route' => 'vet.profile', 'active' => ['vet.profile', 'vet.profile.update'], 'icon' => 'user', 'tab' => true],
        ['label' => 'Alerts', 'route' => 'notifications.index', 'active' => ['notifications.*'], 'icon' => 'bell', 'badge' => 'alerts'],
    ],

    'shelter' => [
        ['label' => 'Overview', 'route' => 'shelter.dashboard', 'active' => ['shelter.dashboard'], 'icon' => 'layout-dashboard', 'tab' => true],
        ['label' => 'Listings', 'route' => 'shelter.listings', 'active' => ['shelter.listings', 'shelter.listings.show', 'shelter.listings.care', 'shelter.listings.status'], 'icon' => 'heart', 'tab' => true],
        ['label' => 'Add pet', 'route' => 'shelter.listings.create', 'active' => ['shelter.listings.create', 'shelter.listings.store'], 'icon' => 'plus', 'tab' => true],
        ['label' => 'Alerts', 'route' => 'notifications.index', 'active' => ['notifications.*'], 'icon' => 'bell', 'badge' => 'alerts'],
    ],

    'admin' => [
        ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard'], 'icon' => 'layout-dashboard', 'tab' => true],
        ['label' => 'User Privileges', 'route' => 'admin.user-privileges', 'active' => ['admin.user-privileges', 'admin.user-privileges.edit', 'admin.user-privileges.update'], 'icon' => 'user', 'tab' => true],
        ['label' => 'Role Matrix', 'route' => 'admin.privileges', 'active' => ['admin.privileges', 'admin.privileges.update'], 'icon' => 'shield', 'tab' => true],
        ['label' => 'Alerts', 'route' => 'notifications.index', 'active' => ['notifications.*'], 'icon' => 'bell', 'badge' => 'alerts'],
        ['label' => 'View site', 'route' => 'home', 'active' => ['home'], 'icon' => 'house'],
    ],

    'search' => [
        ['label' => 'Shop', 'route' => 'products.index'],
        ['label' => 'Care Tips', 'route' => 'care.index'],
        ['label' => 'Adopt', 'route' => 'adopt.index'],
        ['label' => 'Vets', 'route' => 'vets.index'],
        ['label' => 'Shelters', 'route' => 'shelters.index'],
        ['label' => 'About', 'route' => 'about'],
        ['label' => 'Contact', 'route' => 'contact'],
    ],

];

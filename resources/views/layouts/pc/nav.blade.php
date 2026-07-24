@php
    $menus = [
        [
            'title' => 'Home',
            'url' =>  url('/pc/'), 
        ], 
        [
            'title' => 'Templates',
            'url' => url('templates'), 
        ],
        [
            'title' => 'Custom Print',
            'url' => url('custom-print'), 
        ],
        [
            'title' => 'Pricing',
            'url' => url('pricing'), 
        ],
    ];
@endphp

<nav aria-label="Primary" class="hidden lg:flex flex-1 items-center justify-center">
    <ul class="flex items-center gap-1">
        @foreach($menus as $menu)
            <li>
                <a
                    href="{{ $menu['url'] }}"
                    
                    @class([
                        'relative px-4 py-2 rounded-lg text-sm font-semibold transition-colors duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600'
                    ])
                >
                    {{ $menu['title'] }}

                     
                </a>
            </li>
        @endforeach
    </ul>
</nav>
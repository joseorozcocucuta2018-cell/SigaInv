<div @class([
    'filament-footer w-full py-4',
    'fixed bottom-0 left-0 right-0 z-50' => config('filament-footer.position') === 'bottom',
    config('filament-footer.css_classes', '') => config('filament-footer.css_classes'),
])>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
        {!! __('filament-footer::footer.copyright_message', [
            'app_name' => config('app.name', 'Portal'),
            'company_name' => config('filament-footer.company_name', 'Tapp Network'),
            'company_link' => '<a href="' . config('filament-footer.company_url', 'https://tappnetwork.com') . '" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline transition-colors duration-200">' . __('filament-footer::footer.company_link_text', ['company_name' => config('filament-footer.company_name', 'Tapp Network')]) . '</a>',
            'year' => date('Y'),
        ]) !!}
    </p>
    </div>
</div>

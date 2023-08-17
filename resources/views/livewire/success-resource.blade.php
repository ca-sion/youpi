<div>
    <section class="">
        <div class="pb-4 px-4 mx-auto max-w-screen-md text-center">
            <div class="relative p-4 text-center sm:p-5">
                <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 p-2 flex items-center justify-center mx-auto mb-3.5">
                    <i class="bi bi-check"></i>
                </div>
                <h1 class="mb-4 text-4xl font-bold tracking-tight leading-none text-gray-900 lg:mb-6 md:text-5xl xl:text-6xl dark:text-white">Tip top !</h1>
                <p class="font-light text-gray-500 md:text-lg xl:text-xl dark:text-gray-400">La resource a été crée.</p>
            </div>
        </div>
    </section>
    <section class="">
        <div class="px-4 mx-auto max-w-screen-md text-center lg:py-4">
            <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                <div class="flex flex-col gap-2 py-4 sm:gap-6 sm:flex-row sm:items-center">
                    <p class="w-32 text-lg font-normal text-gray-500 sm:text-right dark:text-gray-400 shrink-0">
                      Resource
                    </p>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->computedName }}</h3>
                </div>
                @if ($resource->shareUrl)
                <div class="flex flex-col gap-2 py-4 sm:gap-6 sm:flex-row sm:items-center">
                    <p class="w-32 text-lg font-normal text-gray-500 sm:text-right dark:text-gray-400 shrink-0">
                      Lien
                    </p>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                      <a href="{{ $resource->shareUrl }}" class="hover:underline inline">{{ str($resource->shareUrl)->limit(45) }}</a>
                    </h3>
                </div>
                <div class="flex flex-col gap-2 py-4 sm:gap-6 sm:flex-row sm:items-center">
                    <p class="w-32 text-lg font-normal text-gray-500 sm:text-right dark:text-gray-400 shrink-0">
                      Partager le lien
                    </p>
                    <!-- AddToAny BEGIN -->
                    <div class="a2a_kit a2a_kit_size_32 a2a_default_style inline" data-a2a-url="{{ $resource->shareUrl }}">
                      <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
                      <a class="a2a_button_copy_link"></a>
                      <a class="a2a_button_whatsapp"></a>
                      <a class="a2a_button_telegram"></a>
                      <a class="a2a_button_email"></a>
                      <a class="a2a_button_sms"></a>
                    </div>
                    <script>
                    var a2a_config = a2a_config || {};
                    a2a_config.locale = "fr";
                    </script>
                    <script async src="https://static.addtoany.com/menu/page.js"></script>
                    <!-- AddToAny END -->
                </div>
                @endif

                @if ($resource->text)
                <hr class="my-4">
                <div class="flex flex-col gap-2 py-4 sm:gap-6 sm:flex-row sm:items-center">
                    <p class="w-32 text-lg font-normal text-gray-500 sm:text-right dark:text-gray-400 shrink-0">
                       
                    </p>
                    <div class="format dark:format-invert">{{ new \Illuminate\Support\HtmlString($resource->text) }}</div>
                </div>
                @endif
            </div>
        </div>
    </section>
    <section class="">
        <div class="px-4 mx-auto max-w-screen-md text-center lg:py-4">
            <a class="font-medium text-blue-600 dark:text-blue-500 hover:underline" href="{{ route('welcome') }}">Retour</a>
        </div>
    </section>
</div>

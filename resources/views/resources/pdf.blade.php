@php
    $resource = $getRecord();
@endphp

<!-- PDF.js Express Viewer -->
<div style="margin-top: 10px;">
    <div id='viewer' style="width: 100%;height: 600px;margin:0 auto;"></div>
</div>

@push('head')
    <script src='/vendor/pdfjsexpress/webviewer.min.js'></script>
@endpush

@push('bottom')
    <script>
        WebViewer({
                path: '/vendor/pdfjsexpress', // path to the PDF.js Express'lib' folder on your server
                licenseKey: "{{ config('services.pdfjsexpress.key') }}",
                disableLogs: true,
                initialDoc: "{{ $resource->firstMediaUrl }}",
                disabledElements: [
                    // 'leftPanelButton',
                    'panToolButton',
                    'viewControlsButton',
                    'menuButton',
                    'moreButton',
                ],

            }, document.getElementById('viewer'))
            .then(instance => {
                // now you can access APIs through the WebViewer instance
                const {
                    Core,
                    UI
                } = instance;
                instance.UI.setLanguage("fr");
                instance.UI.setHeaderItems((header) => {
                    header.getHeader('default').push({
                        img: "icon-header-download",
                        index: -1,
                        type: "actionButton",
                        element: 'downloadButton',
                        onClick: () => {
                            instance.UI.downloadPdf({
                                filename: "{{ str()->slug($resource->computedName) }}"
                            })
                        }
                    });
                });
            });
    </script>
@endpush

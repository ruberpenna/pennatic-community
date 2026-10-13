import 'trix';
import 'trix/dist/trix.css';

// wire:navigate replaces the <html> class, so re-apply the collapsed-sidebar hint (see partials/head) on every swap.
const applySidebarState = () => {
    let collapsed = false;
    try {
        collapsed = localStorage.getItem('flux-sidebar-collapsed-desktop') === 'true';
    } catch (e) {}
    document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
};

document.addEventListener('livewire:navigating', (event) => event.detail?.onSwap?.(applySidebarState));
document.addEventListener('livewire:navigated', applySidebarState);

// Editor images (components/editor.blade.php). Editors without data-editor-images accept no files at all;
// other files go in the post's attachment list.
const imageSettings = (editor) => editor.closest('[data-editor-images]')?.dataset;

// Don't caption inserted images with their file name and size.
window.Trix.config.attachments.preview.caption = { name: false, size: false };

document.addEventListener('trix-initialize', (event) => {
    if (! imageSettings(event.target)) {
        event.target.toolbarElement.querySelector('.trix-button-group--file-tools')?.remove();
    }
});

document.addEventListener('trix-file-accept', (event) => {
    const settings = imageSettings(event.target);

    if (! settings) {
        event.preventDefault();

        return;
    }

    const extension = event.file.name.split('.').pop().toLowerCase();

    if (! event.file.type.startsWith('image/') || ! settings.types.split(',').includes(extension)) {
        event.preventDefault();
        alert(settings.wrongType);
    } else if (event.file.size > Number(settings.maxBytes)) {
        event.preventDefault();
        alert(settings.tooLarge);
    }
});

window.pennaUploadEditorImage = (event, $wire) => {
    const attachment = event.attachment;

    if (! attachment.file) {
        return;
    }

    const settings = imageSettings(event.target);
    const fail = () => {
        attachment.remove();
        alert(settings.uploadFailed);
    };

    $wire.upload(
        'editorImage',
        attachment.file,
        () => $wire.storeEditorImage()
            .then((url) => (url ? attachment.setAttributes({ url, href: url }) : fail()))
            .catch(fail),
        fail,
        (progress) => attachment.setUploadProgress(progress.detail.progress),
    );
};

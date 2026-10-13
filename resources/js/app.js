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

// Files are uploaded as post attachments, not embedded in the editor.
document.addEventListener('trix-file-accept', (event) => event.preventDefault());
document.addEventListener('trix-initialize', (event) => {
    event.target.toolbarElement.querySelector('.trix-button-group--file-tools')?.remove();
});

import 'trix';
import 'trix/dist/trix.css';

// wire:navigate replaces the <html> class, so re-apply the saved sidebar state (see partials/head) on every swap.
const applySidebarState = () => {
    let hidden = false;
    try {
        hidden = localStorage.getItem('penna.sidebar') === 'hidden';
    } catch (e) {}
    document.documentElement.classList.toggle('sidebar-hidden', hidden);
};

document.addEventListener('livewire:navigating', (event) => event.detail?.onSwap?.(applySidebarState));
document.addEventListener('livewire:navigated', applySidebarState);

// Files are uploaded as post attachments, not embedded in the editor.
document.addEventListener('trix-file-accept', (event) => event.preventDefault());
document.addEventListener('trix-initialize', (event) => {
    event.target.toolbarElement.querySelector('.trix-button-group--file-tools')?.remove();
});

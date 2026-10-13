import 'trix';
import 'trix/dist/trix.css';

// Files are uploaded as post attachments, not embedded in the editor.
document.addEventListener('trix-file-accept', (event) => event.preventDefault());
document.addEventListener('trix-initialize', (event) => {
    event.target.toolbarElement.querySelector('.trix-button-group--file-tools')?.remove();
});

(() => {
    const fileSelections = new Map();
    const previewUrls = new Map();

    const getPreviewContainer = (input) => input.closest('.field')?.querySelector('[data-admin-image-previews]');
    const getInputError = (input) => {
        const errorId = input.dataset.imageInputErrorTarget;
        return errorId ? document.getElementById(errorId) : null;
    };

    const getAvailableSlots = (input) => {
        const limit = Number(input.dataset.imageLimit || 1);
        const form = input.closest('form');
        const retainedImages = form && !input.hasAttribute('data-replace-existing-image')
            ? [...form.querySelectorAll('[data-admin-existing-image]')].filter((card) => {
                const removal = card.querySelector('[data-admin-image-removal]');
                return !removal?.checked;
            }).length
            : 0;

        return Math.max(0, limit - retainedImages);
    };

    const setFiles = (input, files) => {
        const transfer = new DataTransfer();
        files.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
        fileSelections.set(input, files);
    };

    const renderPreviews = (input) => {
        const container = getPreviewContainer(input);
        if (!container) return;

        (previewUrls.get(input) || []).forEach((url) => URL.revokeObjectURL(url));
        const urls = [];
        container.replaceChildren();
        (fileSelections.get(input) || []).forEach((file, index) => {
            const card = document.createElement('div');
            card.className = 'admin-image-card';

            const image = document.createElement('img');
            image.className = 'admin-image-thumbnail';
            image.alt = file.name;
            image.src = URL.createObjectURL(file);
            urls.push(image.src);

            const filename = document.createElement('small');
            filename.className = 'admin-image-filename';
            filename.textContent = file.name;

            const remove = document.createElement('button');
            remove.className = 'admin-image-remove';
            remove.type = 'button';
            remove.dataset.removeSelectedImage = String(index);
            remove.setAttribute('aria-label', `Remove ${file.name}`);
            remove.title = 'Remove this image';
            remove.textContent = '×';

            card.append(image, filename, remove);
            container.append(card);
        });
        previewUrls.set(input, urls);
    };

    document.querySelectorAll('[data-admin-image-input]').forEach((input) => {
        fileSelections.set(input, []);
        input.addEventListener('change', () => {
            const currentFiles = fileSelections.get(input) || [];
            const addedFiles = [...input.files];
            const nextFiles = [...currentFiles];
            addedFiles.forEach((file) => {
                const isDuplicate = nextFiles.some((selected) => (
                    selected.name === file.name &&
                    selected.size === file.size &&
                    selected.lastModified === file.lastModified
                ));
                if (!isDuplicate) nextFiles.push(file);
            });
            const availableSlots = getAvailableSlots(input);
            const error = getInputError(input);

            if (nextFiles.length > availableSlots) {
                const noun = availableSlots === 1 ? 'image' : 'images';
                if (error) error.textContent = `You can select up to ${availableSlots} ${noun}.`;
                setFiles(input, currentFiles);
                renderPreviews(input);
                return;
            }

            if (error) error.textContent = '';
            setFiles(input, nextFiles);
            renderPreviews(input);
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        const removeSelected = target.closest('[data-remove-selected-image]');
        if (removeSelected) {
            const container = removeSelected.closest('[data-admin-image-previews]');
            const input = container?.closest('.field')?.querySelector('[data-admin-image-input]');
            if (!input) return;

            const index = Number(removeSelected.dataset.removeSelectedImage);
            const files = fileSelections.get(input) || [];
            const nextFiles = files.filter((_, fileIndex) => fileIndex !== index);
            const error = getInputError(input);
            if (error) error.textContent = '';
            setFiles(input, nextFiles);
            renderPreviews(input);
            return;
        }

        const toggleRemoval = target.closest('[data-toggle-image-removal]');
        if (toggleRemoval) {
            const card = toggleRemoval.closest('[data-admin-existing-image]');
            const checkbox = card?.querySelector('[data-admin-image-removal]');
            if (!card || !checkbox) return;

            checkbox.checked = !checkbox.checked;
            card.classList.toggle('is-removing', checkbox.checked);
            toggleRemoval.setAttribute('aria-pressed', String(checkbox.checked));
            toggleRemoval.setAttribute('aria-label', checkbox.checked ? 'Undo image removal' : 'Remove this image');
            toggleRemoval.title = checkbox.checked ? 'Undo removal' : 'Remove this image';
            const input = card.closest('form')?.querySelector('[data-admin-image-input]');
            const error = input ? getInputError(input) : null;
            if (error) error.textContent = '';
        }
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const invalidInput = [...form.querySelectorAll('[data-admin-image-input]')].find((input) => {
                const error = getInputError(input);
                return error?.textContent;
            });
            if (!invalidInput) return;

            event.preventDefault();
            invalidInput.focus();
        });
    });
})();

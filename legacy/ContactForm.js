'use strict';
class ContactForm {

    // Constructor; when creating a new instance, the below code will execute
    constructor(options) {
        // Declare default values
        let defaults = {
            container: document.querySelector('.contact-form'),
            php_file_url: 'contact.php',
            current_pagination_page: 1,
            recaptcha_site_key: null
        };
        // Assign the new options
        this.options = Object.assign(defaults, options);
        // Upload elements object
        this.uploadElements = {};
        // Upload files
        this.uploadFiles = [];
        // Iterate all the file upload elements
        this.container.querySelectorAll('.form-upload').forEach(element => {
            // Create new file upload element
            let input = document.createElement('input');
            input.type = 'file';
            input.multiple = true;
            input.accept = element.dataset.accept;
            input.style.display = 'none';
            // Add to object
            this.uploadElements[element.dataset.name] = input;
            // Append to container
            this.container.append(input);       
        });
        // Trigger event handlers function
        this._eventHandlers();
    }

    // Function that will validate the entire form or an individual element
    validateForm(element = null) {
        // Validation errors
        let errors = false;
        // Retrieve the element(s)
        let elements = element ? [element] : this.container.querySelectorAll('input, select, textarea');
        // Iterate the elements array
        elements.forEach(element => {
            // Check if the element is valid
            if (element.checkValidity() === false && element.required) {
                // Element is invalid, add the error message to the form
                if (!element.parentElement.parentElement.querySelector('.validation-error')) {
                    element.parentElement.insertAdjacentHTML('afterend', `<p class="validation-error">${element.title}</p>`);
                }
                // Add the error class to the element
                element.parentElement.classList.add('error');
                // Focus the element
                element.focus();
                // Update the errors variable
                errors = true;
            } else if (element.checkValidity() === true && element.required) {
                // The element is valid, remove the error message
                if (element.parentElement.parentElement.querySelector('.validation-error')) {
                    element.parentElement.parentElement.querySelector('.validation-error').remove();
                }
                // Remove the error class from the element
                element.parentElement.classList.remove('error');
            }
        });
        return errors;
    }

    processForm(formData) {
        // Add animating spinner to the submit button and disable it
        let btnWidth = this.container.querySelector('[type="submit"]').offsetWidth;
        let btnTxt = this.container.querySelector('[type="submit"]').innerHTML;
        this.container.querySelector('[type="submit"]').innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        this.container.querySelector('[type="submit"]').disabled = true;
        this.container.querySelector('[type="submit"]').style.width = btnWidth + 'px';
        // POST the data to the specified PHP file using AJAX
        fetch(this.phpFileUrl, {
            method: 'POST',
            body: formData,
            cache: 'no-cache'
        }).then(response => response.text()).then(data => {
            // Convert the data to JSON format
            data = JSON.parse(data);
            // If there are errors
            if (data['errors']) {
                // Errors array
                let errors = [];
                // Iterate the list of errors
                Object.entries(data['errors']).forEach(entry => {
                    const [key, value] = entry;
                    // Set custom validity and validate the element
                    if (this.container.querySelector('[name="' + key + '"]')) {
                        this.container.querySelector('[name="' + key + '"]').parentElement.insertAdjacentHTML('afterend', `<p class="validation-error">${value}</p>`);
                        this.container.querySelector('[name="' + key + '"]').parentElement.classList.add('error');
                    }
                    // Add message to errors array
                    errors.push('* ' + value + '<br>');
                });
                // Append error to the errors container
                if (this.container.querySelector('.errors')) {
                    this.container.querySelector('.errors').innerHTML = errors.join('');
                }
                // Update the submit button
                this.container.querySelector('[type="submit"]').disabled = false;
                this.container.querySelector('[type="submit"]').innerHTML = btnTxt;
            }
            // If the response is successful
            if (data['success']) {
                // Set the content of the container with the success message
                this.container.innerHTML = `<div class="success">${data['success']}</div>`;
            }
        });
    }

    // The event handlers function
    _eventHandlers() {
        // Iterate the form elements
        this.container.querySelectorAll('input, textarea, select').forEach(element => {
            // If the element has focus, add the "active" class to the element
            element.addEventListener('focus', () => {
                element.parentElement.querySelectorAll('i').forEach(icon => icon.classList.add('active'));
            });
            // If the element no longer has focus, remove the "active" class from the element
            element.addEventListener('blur', () => {
                element.parentElement.querySelectorAll('i').forEach(icon => icon.classList.remove('active'));
            });
            // When the element has changed, execute the validate form function
            element.addEventListener('change', () => {
                this.validateForm(element);
            });
        });
        // On form submit
        this.container.addEventListener('submit', event => {
            event.preventDefault();
            // If the form contains no errors
            if (!this.validateForm()) {
                // Create a new FormData instance and add the form
                let formData = new FormData(this.container);
                // Iterate the upload files array
                this.uploadFiles.filter(i => i != null).forEach(file => formData.append('files[]', file));
                // Check if recaptcha is enabled
                if (this.recaptchaSiteKey) {
                    // Add the recaptcha response to the form data
                    grecaptcha.ready(() => {
                        grecaptcha.execute(this.recaptchaSiteKey, {action: 'submit'}).then(token => {
                            this.container.insertAdjacentHTML('beforeend', '<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
                            formData.append('gtoken', token);
                            this.processForm(formData); 
                        });
                    });
                } else {
                    // Recaptcha is not enabled, process the form
                    this.processForm(formData);
                }
            }
        });
        // Iterate elements with the "clear-form" class
        this.container.querySelectorAll('.clear-form').forEach(element => element.addEventListener('click', event => {
            event.preventDefault();
            // Reset the form
            this.container.reset();
            // Remove upload elements
            this.container.querySelectorAll('.file').forEach(element => element.remove());
            this.uploadElements = {};
        }));
        // Handle file uploads
        this.container.querySelectorAll('.upload-btn').forEach(element => element.addEventListener('click', event => {
            event.preventDefault();
            // When the user has selected files for upload
            this.uploadElements[element.parentElement.dataset.name].onchange = event => { 
                // Iterate the files
                for (let i = 0; i < event.target.files.length; i++) {
                    let file = event.target.files[i]; 
                    // Add the file to the uploadFiles array
                    let arrIndex = this.uploadFiles.push(file);
                    // If the file is an image
                    if (file.type.includes('image')) {
                        // Read the image and output it on the form, so the user can see what they're uploading
                        let reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = readerEvent => {
                            let content = readerEvent.target.result;
                            element.insertAdjacentHTML('beforebegin', `<div class="file" title="${file.name}" data-index="${arrIndex}"><img src="${content}" width="70" height="70"><a href="#" class="remove-file"><i class="fa-solid fa-xmark"></i></a></div>`);
                            // Add onclick event to remove the file
                            element.parentElement.querySelectorAll('.remove-file').forEach(el => el.onclick = event => {
                                event.preventDefault();
                                // Remove the file from the uploadFiles array
                                this.uploadFiles.splice(el.parentElement.dataset.index - 1, 1, null);
                                // Remove the file element
                                el.parentElement.remove();
                            });
                        };
                    } else {
                        // File is not an image, use an icon instead
                        element.insertAdjacentHTML('beforebegin', `<div class="file" title="${file.name}" data-index="${arrIndex}"><i class="fas fa-file"></i><a href="#" class="remove-file"><i class="fa-solid fa-xmark"></i></a></div>`);
                        // Add onclick event to remove the file
                        element.parentElement.querySelectorAll('.remove-file').forEach(el => el.onclick = event => {
                            event.preventDefault();
                            // Remove the file from the uploadFiles array
                            this.uploadFiles.splice(el.parentElement.dataset.index - 1, 1, null);
                            // Remove the file element
                            el.parentElement.remove();
                        });
                    }
                }
            };
            // Open the upload file dialog
            this.uploadElements[element.parentElement.dataset.name].click();
        }));
    }

    // Get the PHP file the class is associated with
    get phpFileUrl() {
        return this.options.php_file_url;
    }

    // Set the PHP file the class is associated with
    set phpFileUrl(value) {
        this.options.php_file_url = value;
    }

    // Get the recaptcha site key
    get recaptchaSiteKey() {
        return this.options.recaptcha_site_key;
    }

    // Set the recaptcha site key
    set recaptchaSiteKey(value) {
        this.options.recaptcha_site_key = value;
    }

    // Get the container
    get container() {
        return this.options.container;
    }

    // Set the container
    set container(value) {
        this.options.container = value;
    }

}
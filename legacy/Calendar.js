class Calendar {

    constructor(options) {
        // Declare the default options
        let defaults = {
            uid: 1,
            container: document.querySelector('.calendar-container'),
            php_file_url: 'Calendar.php',
            current_date: new Date().toISOString().substring(0, 10),
            size: 'auto',
            display_calendar: true,
            expanded_list: false,
            event_management: true,
            photo_uploads: true
        };
        // Declare the calendar options
        this.options = Object.assign(defaults, options);
        // Modal is not currently open
        this.isModalOpen = false;
        // Set the container position to relative
        this.container.style.position = 'relative';
        // Fetch the calendar
        this.fetchCalendar();
    }

    // Fetch the calendar using AJAX
    fetchCalendar() {
        // Add the loading state
        this.addLoaderIcon();
        // Fetch the calendar
        fetch(this.ajaxUrl, { cache: 'no-store' }).then(response => response.text()).then(data => {
            // Load complete
            // Ouput the response
            this.container.innerHTML = data;
            // Determine the expanded view size
            if (this.container.querySelector('.calendar-expanded-view')) {
                this.container.querySelector('.calendar-expanded-view').classList.remove('normal', 'mini', 'auto');
                this.container.querySelector('.calendar-expanded-view').classList.add(this.size);
            }
            // Ensure the calendar is displayed
            if (this.container.querySelector('.calendar')) {
                // Determine the calendar size
                this.container.querySelector('.calendar').classList.remove('normal', 'mini', 'auto');
                this.container.querySelector('.calendar').classList.add(this.size);
                // Check if event management is enabled
                if (this.container.querySelector('.calendar').dataset.disableEventManagement) {
                    this.options.event_management = false;
                }
                // Check if photo uploads are enabled
                if (this.container.querySelector('.calendar').dataset.disablePhotoUploads) {
                    this.options.photo_uploads = false;
                }
                // Trigger the event handlers
                this._eventHandlers();
            }
            // Remove the loading state
            this.removeLoaderIcon();
        });
    }

    // Function that will open the date select modal
    openDateSelectModal(currentDate) {
        // If there is already a modal open, return false
        if (this.isModalOpen) {
            return false;
        }
        // Update the isModalOpen var
        this.isModalOpen = true;
        // Update the calendar CSS opacity property
        this.container.querySelector('.calendar-header').style.opacity = '.3';
        this.container.querySelector('.calendar-days').style.opacity = '.3';
        // Add the date select template modal to the HTML document
        this.container.insertAdjacentHTML('beforeend', `
            <div class="calendar-modal calendar-date-modal">
                <div class="calendar-event-modal-header">
                    <h5>Select Date</h5>
                    <a href="#" class="close"><svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" /></svg></a>
                </div>
                <div class="calendar-event-modal-content date-select">
                    <h5>Month</h5>
                    <h5>Year</h5>
                    <div class="months"></div>
                    <div class="years"></div>
                </div>
                <div class="calendar-event-modal-footer">
                    <a href="#" class="save">Save</a>
                    <a href="#" class="close">Close</a>
                </div>
            </div>
        `);
        // Select the above modal
        let modalElement = this.container.querySelector('.calendar-date-modal');
        // Determine the current month
        let currentMonth = new Date(currentDate).getMonth() + 1;
        // Iterate every month in the year and add the month to the modal
        for (let month = 1; month <= 12; month++) {
            modalElement.querySelector('.months').insertAdjacentHTML('beforeend', `
                <div class="month${month==currentMonth?' active':''}">${month}</div>
            `);
        }
        // Start year; deduct 40 years from the current year
        let startYear = new Date().getFullYear()-40;
        // End year; add 40 years to the current year
        let endYear = new Date().getFullYear()+40;
        // Current year
        let currentYear = new Date(currentDate).getFullYear();
        // Iterate from the start year to the end year and add the year to the modal
        for (let year = startYear; year <= endYear; year++) {
            modalElement.querySelector('.years').insertAdjacentHTML('beforeend', `
                <div class="year${year==currentYear?' active':''}">${year}</div>
            `);
        }
        // Iterate all months in the modal and add the onclick event, which will add the "active" css class to the corresponding month
        modalElement.querySelectorAll('.month').forEach(element => {
            element.onclick = () => {
                modalElement.querySelectorAll('.month').forEach(element => element.classList.remove('active'));
                element.classList.add('active');
            };
        });
        // Iterate all years in the modal and add the onclick event, which will add the "active" css class to the corresponding year
        modalElement.querySelectorAll('.year').forEach(element => {
            element.onclick = () => {
                modalElement.querySelectorAll('.year').forEach(element => element.classList.remove('active'));
                element.classList.add('active');
            };
        });
        // Position the modal scroll bars
        modalElement.querySelector('.month.active').parentNode.scrollTop = modalElement.querySelector('.month.active').offsetTop - 100;
        modalElement.querySelector('.year.active').parentNode.scrollTop = modalElement.querySelector('.year.active').offsetTop - 100;
        // Save the selected month and year
        modalElement.querySelector('.save').onclick = event => {
            event.preventDefault();
            // Update the current date
            this.currentDate = modalElement.querySelector('.year.active').innerHTML + '-' + modalElement.querySelector('.month.active').innerHTML + '-01';
            // Remove the modal
            modalElement.remove();
            // Update the calendar CSS opacity property
            this.container.querySelector('.calendar-header').style.opacity = '1';
            this.container.querySelector('.calendar-days').style.opacity = '1';
            // Fetch the calendar
            this.fetchCalendar();
            // Modal is no longer open
            this.isModalOpen = false;
        };
        this.closeEventHandler(modalElement);
    }

    // Function that will open the event list modal
    openEventModal(startDate, endDate, eventsList, dateLabel) {
        // If there is already a modal open, return false
        if (this.isModalOpen) {
            return false;
        }
        // Update the isModalOpen var
        this.isModalOpen = true;
        // Update the calendar CSS opacity property
        this.container.querySelector('.calendar-header').style.opacity = '.3';
        this.container.querySelector('.calendar-days').style.opacity = '.3';
        // Add the date select template modal to the HTML document
        this.container.insertAdjacentHTML('beforeend', `
            <div class="calendar-modal calendar-event-modal">
                <div class="calendar-event-modal-header">
                    <h5>${dateLabel}</h5>
                    <a href="#" class="close"><svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" /></svg></a>
                </div>
                <div class="calendar-event-modal-content">
                ${eventsList}
                </div>
                <div class="calendar-event-modal-footer">
                    <a href="#" class="close">Close</a>
                </div>
            </div>
        `);
        // Select the above modal
        let modalElement = this.container.querySelector('.calendar-event-modal');
        // Check if event management is enabled
        if (this.options.event_management) {
            // Iterate all events   
            modalElement.querySelectorAll('.events .event').forEach(element => {
                // Edit button onclick event
                element.querySelector('.edit').onclick = event => {
                    event.preventDefault();
                    // Remove the current modal
                    modalElement.remove();
                    // Modal is no longer open
                    this.isModalOpen = false;
                    // Edit object
                    let editObj = {
                        id: element.dataset.id,
                        title: element.dataset.title,
                        datestart: element.dataset.start,
                        dateend: element.dataset.end,
                        color: element.dataset.color,
                        description: element.querySelector('.description') ? element.querySelector('.description').innerHTML : '',
                        recurring: element.dataset.recurring,
                        redirect_url: element.dataset.redirectUrl
                    };
                    // Open the add event modal
                    this.openAddEventModal(startDate, endDate, editObj);               
                };
                // Delete button onclick event
                element.querySelector('.delete').onclick = event => {
                    event.preventDefault();
                    // Remove the current modal
                    modalElement.remove();
                    // Modal is no longer open
                    this.isModalOpen = false;
                    // Open the delete event modal
                    this.openDeleteEventModal(element.dataset.id);               
                };
            });
            // Add the add event button to the modal footer
            modalElement.querySelector('.calendar-event-modal-footer').insertAdjacentHTML('afterbegin', '<a href="#" class="add_event">Add Event</a>');
            // Add event button onclick event
            modalElement.querySelector('.add_event').onclick = event => {
                event.preventDefault();
                // Remove the current modal
                modalElement.remove();
                // Modal is no longer open
                this.isModalOpen = false;
                // Open the add event modal
                this.openAddEventModal(startDate, endDate);
            };  
        }
        this.closeEventHandler(modalElement);
    }

    // Function that will open the add event modal 
    openAddEventModal(startDate, endDate, edit) {
        // If there is already a modal open, return false
        if (this.isModalOpen || !this.options.event_management) {
            return false;
        }
        // Update the isModalOpen var
        this.isModalOpen = true;
        // Update the calendar CSS opacity property
        this.container.querySelector('.calendar-header').style.opacity = '.3';
        this.container.querySelector('.calendar-days').style.opacity = '.3';
        // Create the date variables
        let startDateStr, endDateStr, t;
        // If editing an event
        if (edit) {
            // Update the start date string
            t = edit.datestart.split(/[- :]/);
            startDateStr = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5])).toISOString();
        } else {
            startDateStr = new Date(startDate).toISOString();
        }
        if (edit) {
            // Update the end date string
            t = edit.dateend.split(/[- :]/);
            endDateStr = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5])).toISOString();           
        } else {
            endDateStr = new Date(endDate).toISOString();
        }
        startDateStr = startDateStr.substring(0,startDateStr.length-1);
        endDateStr = endDateStr.substring(0,endDateStr.length-1);
        // Get the list of colors
        let colors = this.container.querySelector('.calendar').dataset.colors.split(',');
        // Add the add event modal template to the HTML document
        this.container.insertAdjacentHTML('beforeend', `
            <div class="calendar-modal calendar-add-event-modal">
                <div class="calendar-event-modal-header">
                    <h5>${edit ? 'Update' : 'Add'} Event</h5>
                    <a href="#" class="close"><svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" /></svg></a>
                </div>
                <div class="calendar-event-modal-content">
                    <form>

                        <label for="title"><span class="required">*</span> Title</label>
                        <input id="title" name="title" type="text" placeholder="Title" value="${edit ? edit.title : ''}">

                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Description">${edit ? edit.description : ''}</textarea>

                        <div class="wrapper">
                            <div class="column">
                                <label for="startdate"><span class="required">*</span> Start Date</label>
                                <input id="startdate" name="startdate" type="datetime-local" value="${startDateStr}">
                            </div>
                            <div class="column">
                                <label for="enddate"><span class="required">*</span> End Date</label>
                                <input id="enddate" name="enddate" type="datetime-local" value="${endDateStr}">
                            </div>
                        </div>

                        <div class="wrapper">
                            <div class="column">
                                <label for="recurring">Recurring</label>
                                <select id="recurring" name="recurring">
                                    <option value="never"${edit && edit.recurring == 'never' ? ' selected' : ''}>Never</option>
                                    <option value="daily"${edit && edit.recurring == 'daily' ? ' selected' : ''}>Daily</option>
                                    <option value="weekly"${edit && edit.recurring == 'weekly' ? ' selected' : ''}>Weekly</option>
                                    <option value="monthly"${edit && edit.recurring == 'monthly' ? ' selected' : ''}>Monthly</option>
                                    <option value="yearly"${edit && edit.recurring == 'yearly' ? ' selected' : ''}>Yearly</option>
                                </select>
                            </div>
                            <div class="column">
                                <label for="redirect_url">URL</label>
                                <input id="redirect_url" name="redirect_url" type="text" placeholder="URL" value="${edit ? edit.redirect_url : ''}">
                            </div>
                        </div>

                        <label for="color">Color</label>
                        <div class="colors">
                            ${colors.map((color, index) => `<label class="color"><input type="radio" name="color" value="${color}"${(edit && edit.color == color) || (!edit && index == 0) ? ' checked' : ''}${index == 0 ? ' id="color"' : ''}><span style="background-color:${color}"></span></label>`).join('')}
                        </div>

                        ${this.options.photo_uploads ? `
                        <label for="photo">Photo</label>
                        <label class="file-input">
                            <span class="file-icon"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19,19H5V5H19M19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M13.96,12.29L11.21,15.83L9.25,13.47L6.5,17H17.5L13.96,12.29Z" /></svg></span>
                            <span class="file-name">Select Photo...</span>
                            <input id="photo" name="photo" type="file" placeholder="Photo">
                        </label>
                        ` : ''}

                        <input type="hidden" name="eventid" value="${edit ? edit.id : ''}"> 

                        <span id="msg"></span>  

                    </form>
                </div>
                <div class="calendar-event-modal-footer">
                    <a href="#" class="add_event">${edit ? 'Update' : 'Add'} Event</a>
                    <a href="#" class="close">Cancel</a>
                </div>
            </div>
        `);
        // Select the modal element
        let modalElement = this.container.querySelector('.calendar-add-event-modal'); 
        // Check if photo uploads are enabled and if so add the event listener to update the file name
        if (this.options.photo_uploads) {
            modalElement.querySelector('#photo').onchange = event => {
                modalElement.querySelector('.file-name').innerHTML = event.target.files[0].name;
            };
        }
        // Add event button onclick event
        modalElement.querySelector('.add_event').onclick = event => {
            event.preventDefault();
            // Disable the button
            modalElement.querySelector('.add_event').disabled = true;
            // Use AJAX to add a new event to the calendar
            fetch(this.ajaxUrl, { cache: 'no-store', method: 'POST', body: new FormData(modalElement.querySelector('form')) }).then(response => response.text()).then(data => {
                // Check if the response us "success"
                if (data.includes('success')) {
                    // Remove the modal
                    modalElement.remove();
                    // Fetch the calendar
                    this.fetchCalendar();
                    // Modal is no longer open
                    this.isModalOpen = false;  
                } else {
                    // Something went wrong... output the errors
                    modalElement.querySelector('#msg').innerHTML = data;
                    // Enable the button
                    modalElement.querySelector('.add_event').disabled = false;
                }
            });
        };
        this.closeEventHandler(modalElement);     
    }

    // Function that will open the delete event modal
    openDeleteEventModal(id) {
        // If there is already a modal open, return false
        if (this.isModalOpen || !this.options.event_management) {
            return false;
        }
        // Update the isModalOpen var
        this.isModalOpen = true;
        // Update the calendar CSS opacity property
        this.container.querySelector('.calendar-header').style.opacity = '.3';
        this.container.querySelector('.calendar-days').style.opacity = '.3';
        // Add the delete event modal template to the HTML document
        this.container.insertAdjacentHTML('beforeend', `
            <div class="calendar-modal calendar-delete-event-modal">
                <div class="calendar-event-modal-header">
                    <h5>Delete Event</h5>
                    <a href="#" class="close"><svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" /></svg></a>
                </div>
                <div class="calendar-event-modal-content">
                    <p>Are you sure you want to delete this event?</p>
                </div>
                <div class="calendar-event-modal-footer">
                    <a href="#" class="delete_event">Delete</a>
                    <a href="#" class="close">Cancel</a>
                </div>
            </div>
        `);
        // Select the modal element
        let modalElement = this.container.querySelector('.calendar-delete-event-modal');
        // Delete event button onclick event
        modalElement.querySelector('.delete_event').onclick = event => {
            event.preventDefault();
            // Disable the button
            modalElement.querySelector('.delete_event').disabled = true;
            // Use AJAX to delete the event
            fetch(this.ajaxUrl + "&delete_event=" + id, { cache: 'no-store' }).then(response => response.text()).then(data => {
                // Remove the modal
                modalElement.remove();
                // Fetch the calendar
                this.fetchCalendar();
                // Modal is no longer open
                this.isModalOpen = false;  
            });
        };
        this.closeEventHandler(modalElement);     
    }

    // Function that will add the close event handler to the modal
    closeEventHandler(modalElement) {
        // Close button onclick event
        modalElement.querySelectorAll('.close').forEach(element => element.onclick = event => {
            event.preventDefault();
            // Remove the modal
            modalElement.remove();
            // Update the calendar CSS opacity property
            this.container.querySelector('.calendar-header').style.opacity = '1';
            this.container.querySelector('.calendar-days').style.opacity = '1';
            // Modal is no longer open
            this.isModalOpen = false;
        });
    }

    // Function that will add the loading state
    addLoaderIcon() {
        // If the loading state has already been intialized, return and prevent further execution
        if (this.container.querySelector('.calendar-loader') || !this.container.querySelector('.calendar')) {
            return;
        }
        // Update the calendar CSS opacity property
        this.container.querySelector('.calendar-header').style.opacity = '.3';
        this.container.querySelector('.calendar-days').style.opacity = '.3';
        // Add the loader element to the HTML document
        this.container.querySelector('.calendar').insertAdjacentHTML('beforeend', `
            <div class="calendar-loader">
                <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
            </div>
        `);        
        // Add size to the loader element
        this.container.querySelector('.calendar-loader').classList.add(this.size);
    }

    // Function that will remove the loading state
    removeLoaderIcon() {
        if (this.container.querySelector('.calendar-loader')) {
            this.container.querySelector('.calendar-loader').remove();
        }
    }

    // Function that will refresh the calendar
    update() {
        this.fetchCalendar();
    }

    // Event handlers for all calendar elements
    _eventHandlers() {
        // Calendar month previous button onclick event
        this.container.querySelector('.calendar-header .prev').onclick = event => {
            event.preventDefault();
            // Update the current date
            this.currentDate = this.container.querySelector('.calendar-header .prev').dataset.date;
            // Fetch calendar
            this.fetchCalendar();
        };
        // Calendar month next button onclick event
        this.container.querySelector('.calendar-header .next').onclick = event => {
            event.preventDefault();
            // Update the current date
            this.currentDate = this.container.querySelector('.calendar-header .next').dataset.date;
            // Fetch calendar
            this.fetchCalendar();
        };
        // Calendar month next button onclick event
        this.container.querySelector('.calendar-header .today').onclick = event => {
            event.preventDefault();
            // Update the current date
            this.currentDate = new Date().toISOString().substring(0, 10);
            // Fetch calendar
            this.fetchCalendar();
        };
        // Refresh the calendar
        this.container.querySelector('.calendar-header .refresh').onclick = event => {
            event.preventDefault();
            // Fetch calendar
            this.fetchCalendar();
        };
        // Calendar month current button onclick event
        this.container.querySelector('.calendar-header .current').onclick = event => {
            event.preventDefault();
            // Open the date select modal
            this.openDateSelectModal(this.currentDate);
        };
        // Iterate all the day elements, exluding the ignored elements
        this.container.querySelectorAll('.calendar-days .day_num:not(.ignore)').forEach(element => {
            // Add onclick event
            element.onclick = () => {
                // If there is already a modal open, return and prevent further execution
                if (this.isModalOpen) {
                    return;
                }
                // Add the loading state
                this.addLoaderIcon();
                // Retrieve all events for the selected day
                fetch(this.ajaxUrl + '&events_list=' + element.dataset.date, { cache: 'no-store' }).then(response => response.text()).then(data => {
                    // Remove the loading state element
                    this.removeLoaderIcon();
                    // Open the events list modal
                    this.openEventModal(element.dataset.date, element.dataset.date, data, element.dataset.label);
                });
            };
        });
    }

    // Determine the AJAX URL
    get ajaxUrl() {
        let url = `${this.phpFileUrl}${this.phpFileUrl.includes('?') ? '&' : '?'}uid=${this.uid}`;
        url += 'current_date' in this.options ? `&current_date=${this.currentDate}` : '';
        url += 'size' in this.options ? `&size=${this.size}` : '';
        url += this.expandedList ? `&expanded_list=${this.expandedList}` : '';
        url += this.displayCalendar ? `&display_calendar=${this.displayCalendar}` : '';
        return url;       
    }

    // Get: Unique ID
    get uid() {
        return this.options.uid;
    }

    // Set: Unique ID
    set uid(value) {
        this.options.uid = value;
    }

    // Get: PHP calendar file URL
    get phpFileUrl() {
        return this.options.php_file_url;
    }

    // Set: PHP calendar file URL
    set phpFileUrl(value) {
        this.options.php_file_url = value;
    }

    // Get: HTML DOM calendar container
    get container() {
        return this.options.container;
    }

    // Set: HTML DOM calendar container
    set container(value) {
        this.options.container = value;
    }

    // Get: current calendar date
    get currentDate() {
        return this.options.current_date;
    }

    // Set: current calendar date
    set currentDate(value) {
        this.options.current_date = value;
    }

    // Get: calendar size (normal|mini)
    get size() {
        return this.options.size;
    }

    // Set: calendar size (normal|mini)
    set size(value) {
        this.options.size = value;
    }

    // Get: display calendar (true|false)
    get displayCalendar() {
        return this.options.display_calendar;
    }

    // Set: display calendar (true|false)
    set displayCalendar(value) {
        this.options.display_calendar = value;
    }

    // Get: expanded list (true|false)
    get expandedList() {
        return this.options.expanded_list;
    }

    // Set: expanded list (true|false)
    set expandedList(value) {
        this.options.expanded_list = value;
    }

}
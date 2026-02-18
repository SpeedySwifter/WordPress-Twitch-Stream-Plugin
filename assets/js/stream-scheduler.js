/**
 * Visual Stream Scheduler JavaScript
 */

(function($) {
    'use strict';

    // Main Stream Scheduler class
    var TwitchStreamScheduler = {
        calendar: null,
        currentView: 'calendar',
        currentChannel: '',
        streams: [],
        settings: {},
        modal: null,

        init: function() {
            this.bindEvents();
            this.initCalendar();
            this.loadInitialData();
            this.initModals();
            this.initDragAndDrop();
        },

        bindEvents: function() {
            var self = this;

            // View switchers
            $(document).on('click', '.twitch-view-btn', function(e) {
                e.preventDefault();
                var view = $(this).data('view');
                self.switchView(view);
            });

            // Add stream button
            $(document).on('click', '.twitch-add-stream-btn', function(e) {
                e.preventDefault();
                self.showStreamModal();
            });

            // Bulk actions button
            $(document).on('click', '.twitch-bulk-actions-btn', function(e) {
                e.preventDefault();
                self.showBulkActions();
            });

            // Clear filters
            $(document).on('click', '.twitch-clear-filters-btn', function(e) {
                e.preventDefault();
                self.clearFilters();
            });

            // Filter changes
            $(document).on('change', '.twitch-date-filter, .twitch-status-filter, .twitch-category-filter', function() {
                self.applyFilters();
            });

            // Stream item clicks
            $(document).on('click', '.twitch-stream-list-item', function(e) {
                e.preventDefault();
                var streamId = $(this).data('stream-id');
                self.editStream(streamId);
            });

            // Stream actions
            $(document).on('click', '.twitch-remind-btn', function(e) {
                e.preventDefault();
                var streamId = $(this).data('stream-id');
                self.setReminder(streamId);
            });

            $(document).on('click', '.twitch-calendar-btn', function(e) {
                e.preventDefault();
                var streamId = $(this).data('stream-id');
                self.addToCalendar(streamId);
            });

            // Modal events
            $(document).on('click', '.twitch-modal-close', function(e) {
                e.preventDefault();
                self.hideModal();
            });

            $(document).on('click', '.twitch-modal', function(e) {
                if (e.target === this) {
                    self.hideModal();
                }
            });

            // Form submissions
            $(document).on('submit', '.twitch-scheduler-form', function(e) {
                e.preventDefault();
                self.saveStream($(this));
            });

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Escape to close modal
                if (e.key === 'Escape') {
                    self.hideModal();
                }

                // Ctrl/Cmd + N to add new stream
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    self.showStreamModal();
                }

                // Ctrl/Cmd + F to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    $('.twitch-search-input').focus();
                }
            });
        },

        initCalendar: function() {
            var self = this;
            var calendarEl = document.getElementById('twitch-calendar-view');

            if (!calendarEl) return;

            this.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: 'auto',
                editable: true,
                droppable: true,
                eventResizableFromStart: true,
                dayMaxEvents: 3,
                moreLinkClick: 'popover',

                // Event rendering
                eventContent: function(arg) {
                    return self.renderEventContent(arg);
                },

                // Event clicking
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    self.editStream(info.event.id);
                },

                // Event dropping (drag and drop)
                eventDrop: function(info) {
                    self.updateStreamTime(info.event.id, info.event.start, info.event.end);
                },

                // Event resizing
                eventResize: function(info) {
                    self.updateStreamTime(info.event.id, info.event.start, info.event.end);
                },

                // Date clicking
                dateClick: function(info) {
                    if (info.view.type === 'dayGridMonth') {
                        self.calendar.changeView('timeGridDay', info.dateStr);
                    }
                },

                // Day rendering
                dayCellDidMount: function(arg) {
                    // Add custom classes or content to day cells
                    if (arg.date.getDay() === 0 || arg.date.getDay() === 6) {
                        arg.el.classList.add('twitch-weekend-day');
                    }
                },

                // Event mouse enter/leave
                eventMouseEnter: function(info) {
                    info.el.style.transform = 'scale(1.02)';
                },

                eventMouseLeave: function(info) {
                    info.el.style.transform = 'scale(1)';
                },

                // Loading state
                loading: function(isLoading) {
                    if (isLoading) {
                        self.showCalendarLoading();
                    } else {
                        self.hideCalendarLoading();
                    }
                }
            });

            this.calendar.render();
        },

        renderEventContent: function(arg) {
            var event = arg.event;
            var streamData = event.extendedProps;

            return {
                html: '<div class="twitch-calendar-event">' +
                    '<div class="twitch-event-time">' + arg.timeText + '</div>' +
                    '<div class="twitch-event-title">' + event.title + '</div>' +
                    '<div class="twitch-event-channel">' + (streamData.channel || '') + '</div>' +
                    '</div>'
            };
        },

        loadInitialData: function() {
            var $scheduler = $('.twitch-stream-scheduler');
            this.currentChannel = $scheduler.data('channel') || '';
            this.settings = twitchStreamScheduler.settings;

            this.loadStreams();
            this.loadStats();
        },

        loadStreams: function() {
            var self = this;
            var $scheduler = $('.twitch-stream-scheduler');
            var channel = $scheduler.data('channel');
            var startDate = $('#twitch-start-date').val();
            var endDate = $('#twitch-end-date').val();
            var status = $('.twitch-status-filter').val();
            var category = $('.twitch-category-filter').val();
            var limit = $scheduler.data('limit') || 50;

            $.ajax({
                url: twitchStreamScheduler.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_stream_scheduler',
                    scheduler_action: 'get_streams',
                    channel: channel,
                    start_date: startDate,
                    end_date: endDate,
                    status: status,
                    category: category,
                    limit: limit,
                    nonce: twitchStreamScheduler.nonce
                },
                beforeSend: function() {
                    self.showLoading(true);
                },
                success: function(response) {
                    if (response.success) {
                        self.streams = response.data.streams;
                        self.updateViews();
                    } else {
                        self.showError('Failed to load streams');
                    }
                },
                error: function() {
                    self.showError('AJAX error occurred');
                },
                complete: function() {
                    self.showLoading(false);
                }
            });
        },

        updateViews: function() {
            this.updateCalendarView();
            this.updateListView();
            this.updateTimelineView();
            this.updateSidebar();
        },

        updateCalendarView: function() {
            if (!this.calendar) return;

            var events = this.streams.map(function(stream) {
                var categoryInfo = twitchStreamScheduler.categories[stream.category] || {
                    name: 'General',
                    color: '#9146ff'
                };

                return {
                    id: stream.id,
                    title: stream.title,
                    start: stream.start_time,
                    end: stream.end_time,
                    backgroundColor: categoryInfo.color,
                    borderColor: categoryInfo.color,
                    textColor: '#ffffff',
                    extendedProps: {
                        channel: stream.channel,
                        category: stream.category,
                        status: stream.status,
                        description: stream.description
                    },
                    className: 'twitch-stream-event twitch-status-' + stream.status
                };
            });

            this.calendar.removeAllEvents();
            this.calendar.addEventSource(events);
        },

        updateListView: function() {
            var $listView = $('.twitch-list-view');
            var html = '<div class="twitch-streams-list">';

            if (this.streams.length === 0) {
                html += '<p class="twitch-no-streams">No streams found for the selected filters.</p>';
            } else {
                this.streams.forEach(function(stream) {
                    html += this.renderListItem(stream);
                }, this);
            }

            html += '</div>';
            $listView.html(html);
        },

        updateTimelineView: function() {
            var $timelineView = $('.twitch-timeline-view');
            var html = '<div class="twitch-timeline-container">';

            if (this.streams.length === 0) {
                html += '<p class="twitch-no-streams">No streams found for the selected filters.</p>';
            } else {
                html += '<div class="twitch-timeline-line"></div>';

                this.streams.forEach(function(stream) {
                    html += this.renderTimelineItem(stream);
                }, this);
            }

            html += '</div>';
            $timelineView.html(html);
        },

        updateSidebar: function() {
            this.updateUpcomingStreams();
            this.updateStats();
        },

        updateUpcomingStreams: function() {
            var upcomingStreams = this.streams.filter(function(stream) {
                return stream.status === 'scheduled';
            }).slice(0, 5);

            var $upcomingList = $('.twitch-upcoming-list');
            var html = '';

            if (upcomingStreams.length === 0) {
                html = '<p class="twitch-no-upcoming">No upcoming streams</p>';
            } else {
                upcomingStreams.forEach(function(stream) {
                    html += '<div class="twitch-upcoming-item" data-stream-id="' + stream.id + '">' +
                        '<div class="twitch-stream-date">' + this.formatDate(stream.start_time, 'M j') + '</div>' +
                        '<div class="twitch-stream-clock">' + this.formatDate(stream.start_time, 'g:i A') + '</div>' +
                        '<div class="twitch-stream-title">' + stream.title + '</div>' +
                        '</div>';
                }, this);
            }

            $upcomingList.html(html);
        },

        updateStats: function() {
            var stats = this.calculateStats();

            $('#total-streams').text(stats.total);
            $('#live-streams').text(stats.live);
            $('#hours-streamed').text(stats.hours);
        },

        calculateStats: function() {
            var total = this.streams.length;
            var live = this.streams.filter(function(s) { return s.status === 'live'; }).length;
            var completedStreams = this.streams.filter(function(s) { return s.status === 'completed'; });
            var totalMinutes = 0;

            completedStreams.forEach(function(stream) {
                var start = new Date(stream.start_time);
                var end = new Date(stream.end_time);
                totalMinutes += (end - start) / (1000 * 60);
            });

            return {
                total: total,
                live: live,
                hours: Math.round(totalMinutes / 60 * 10) / 10
            };
        },

        switchView: function(view) {
            // Update button states
            $('.twitch-view-btn').removeClass('active');
            $('.twitch-view-btn[data-view="' + view + '"]').addClass('active');

            // Hide all views
            $('.twitch-calendar-view, .twitch-list-view, .twitch-timeline-view').hide();

            // Show selected view
            $('.twitch-' + view + '-view').show();

            this.currentView = view;

            // Update calendar if switching to calendar view
            if (view === 'calendar' && this.calendar) {
                this.calendar.updateSize();
            }
        },

        showStreamModal: function(streamId = null) {
            var modalHtml = '<div class="twitch-scheduler-modal">' +
                '<div class="twitch-modal-content">' +
                '<div class="twitch-modal-header">' +
                '<h3 class="twitch-modal-title">' + (streamId ? 'Edit Stream' : 'Schedule New Stream') + '</h3>' +
                '<button class="twitch-modal-close">&times;</button>' +
                '</div>' +
                '<form class="twitch-scheduler-form">' +
                '<div class="twitch-modal-body">' +
                this.renderStreamForm(streamId) +
                '</div>' +
                '<div class="twitch-modal-actions">' +
                '<button type="button" class="twitch-modal-cancel">Cancel</button>' +
                '<button type="submit" class="twitch-modal-save">Save Stream</button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</div>';

            $('body').append(modalHtml);
            this.initFormValidation();
            this.initDateTimePickers();
        },

        renderStreamForm: function(streamId) {
            var stream = streamId ? this.streams.find(function(s) { return s.id == streamId; }) : null;
            var channel = $('.twitch-stream-scheduler').data('channel') || '';

            return '<div class="twitch-scheduler-form">' +
                '<div class="twitch-form-row">' +
                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Channel</label>' +
                '<input type="text" name="channel" class="twitch-form-input" value="' + (stream ? stream.channel : channel) + '" required>' +
                '</div>' +
                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Stream Type</label>' +
                '<select name="stream_type" class="twitch-form-select">' +
                this.renderSelectOptions(twitchStreamScheduler.streamTypes, stream ? stream.stream_type : 'live') +
                '</select>' +
                '</div>' +
                '</div>' +

                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Title</label>' +
                '<input type="text" name="title" class="twitch-form-input" value="' + (stream ? stream.title : '') + '" required>' +
                '</div>' +

                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Description</label>' +
                '<textarea name="description" class="twitch-form-textarea" rows="3">' + (stream ? stream.description : '') + '</textarea>' +
                '</div>' +

                '<div class="twitch-form-row">' +
                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Start Time</label>' +
                '<input type="datetime-local" name="start_time" class="twitch-form-input" value="' + (stream ? this.formatDateTime(stream.start_time) : '') + '" required>' +
                '</div>' +
                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">End Time</label>' +
                '<input type="datetime-local" name="end_time" class="twitch-form-input" value="' + (stream ? this.formatDateTime(stream.end_time) : '') + '" required>' +
                '</div>' +
                '</div>' +

                '<div class="twitch-form-row">' +
                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Timezone</label>' +
                '<select name="timezone" class="twitch-form-select">' +
                this.renderTimezoneOptions(stream ? stream.timezone : 'UTC') +
                '</select>' +
                '</div>' +
                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Category</label>' +
                '<select name="category" class="twitch-form-select">' +
                '<option value="">Select Category</option>' +
                this.renderSelectOptions(twitchStreamScheduler.categories, stream ? stream.category : '') +
                '</select>' +
                '</div>' +
                '</div>' +

                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Tags (comma-separated)</label>' +
                '<input type="text" name="tags" class="twitch-form-input" value="' + (stream ? stream.tags : '') + '" placeholder="gaming, live, community">' +
                '</div>' +

                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Thumbnail URL</label>' +
                '<input type="url" name="thumbnail_url" class="twitch-form-input" value="' + (stream ? stream.thumbnail_url : '') + '">' +
                '</div>' +

                '<div class="twitch-form-group">' +
                '<label class="twitch-checkbox-label">' +
                '<input type="checkbox" name="is_recurring" ' + (stream && stream.is_recurring ? 'checked' : '') + '> ' +
                '<span class="twitch-checkbox-text">Recurring Stream</span>' +
                '</label>' +
                '</div>' +

                '<div class="twitch-recurring-options" style="display: ' + (stream && stream.is_recurring ? 'block' : 'none') + '">' +
                '<div class="twitch-form-row">' +
                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">Recurring Pattern</label>' +
                '<select name="recurring_pattern" class="twitch-form-select">' +
                '<option value="daily">Daily</option>' +
                '<option value="weekly">Weekly</option>' +
                '<option value="monthly">Monthly</option>' +
                '</select>' +
                '</div>' +
                '<div class="twitch-form-group">' +
                '<label class="twitch-form-label">End Date</label>' +
                '<input type="date" name="recurring_end_date" class="twitch-form-input" value="' + (stream ? this.formatDate(stream.recurring_end_date) : '') + '">' +
                '</div>' +
                '</div>' +
                '</div>' +

                (streamId ? '<input type="hidden" name="stream_id" value="' + streamId + '">' : '') +
                '</div>';
        },

        renderSelectOptions: function(options, selected) {
            var html = '';
            $.each(options, function(key, option) {
                var label = typeof option === 'object' ? option.name : option;
                var isSelected = key === selected ? 'selected' : '';
                html += '<option value="' + key + '" ' + isSelected + '>' + label + '</option>';
            });
            return html;
        },

        renderTimezoneOptions: function(selected) {
            var html = '';
            $.each(twitchStreamScheduler.timezones, function(key, label) {
                var isSelected = key === selected ? 'selected' : '';
                html += '<option value="' + key + '" ' + isSelected + '>' + label + '</option>';
            });
            return html;
        },

        initFormValidation: function() {
            // Add form validation
            $('.twitch-scheduler-form').validate({
                rules: {
                    title: 'required',
                    channel: 'required',
                    start_time: 'required',
                    end_time: 'required'
                },
                messages: {
                    title: 'Please enter a stream title',
                    channel: 'Please enter a channel name',
                    start_time: 'Please select a start time',
                    end_time: 'Please select an end time'
                }
            });
        },

        initDateTimePickers: function() {
            // Initialize datetime pickers if available
            if ($.fn.datetimepicker) {
                $('input[name="start_time"], input[name="end_time"]').datetimepicker({
                    format: 'Y-m-d H:i',
                    step: 30
                });
            }
        },

        saveStream: function($form) {
            var self = this;
            var formData = $form.serializeArray();
            var streamData = {};

            $.each(formData, function(i, field) {
                streamData[field.name] = field.value;
            });

            // Convert checkbox values
            streamData.is_recurring = $form.find('[name="is_recurring"]').is(':checked') ? 1 : 0;

            $.ajax({
                url: twitchStreamScheduler.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_stream_scheduler',
                    scheduler_action: 'save_stream',
                    stream_data: streamData,
                    nonce: twitchStreamScheduler.nonce
                },
                beforeSend: function() {
                    self.showModalLoading(true);
                },
                success: function(response) {
                    if (response.success) {
                        self.hideModal();
                        self.loadStreams();
                        self.showMessage(response.data.message || 'Stream saved successfully', 'success');
                    } else {
                        var errors = Array.isArray(response.data) ? response.data.join(', ') : 'Failed to save stream';
                        self.showMessage(errors, 'error');
                    }
                },
                error: function() {
                    self.showMessage('AJAX error occurred', 'error');
                },
                complete: function() {
                    self.showModalLoading(false);
                }
            });
        },

        editStream: function(streamId) {
            this.showStreamModal(streamId);
        },

        updateStreamTime: function(streamId, start, end) {
            var self = this;

            $.ajax({
                url: twitchStreamScheduler.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_stream_scheduler',
                    scheduler_action: 'update_stream',
                    stream_id: streamId,
                    start_time: this.formatDateTime(start),
                    end_time: this.formatDateTime(end),
                    nonce: twitchStreamScheduler.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showMessage('Stream time updated', 'success');
                    } else {
                        self.showMessage('Failed to update stream time', 'error');
                        // Revert the change
                        self.loadStreams();
                    }
                },
                error: function() {
                    self.showMessage('AJAX error occurred', 'error');
                    self.loadStreams();
                }
            });
        },

        deleteStream: function(streamId) {
            if (!confirm(twitchStreamScheduler.strings.deleteConfirm)) {
                return;
            }

            var self = this;

            $.ajax({
                url: twitchStreamScheduler.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_stream_scheduler',
                    scheduler_action: 'delete_stream',
                    stream_id: streamId,
                    nonce: twitchStreamScheduler.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.loadStreams();
                        self.showMessage('Stream deleted successfully', 'success');
                    } else {
                        self.showMessage('Failed to delete stream', 'error');
                    }
                },
                error: function() {
                    self.showMessage('AJAX error occurred', 'error');
                }
            });
        },

        setReminder: function(streamId) {
            var self = this;

            $.ajax({
                url: twitchStreamScheduler.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_stream_scheduler',
                    scheduler_action: 'set_reminder',
                    stream_id: streamId,
                    reminder_time: 15, // 15 minutes before
                    nonce: twitchStreamScheduler.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showMessage('Reminder set successfully', 'success');
                    } else {
                        self.showMessage('Failed to set reminder', 'error');
                    }
                },
                error: function() {
                    self.showMessage('AJAX error occurred', 'error');
                }
            });
        },

        addToCalendar: function(streamId) {
            var stream = this.streams.find(function(s) { return s.id == streamId; });
            if (!stream) return;

            var calendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE' +
                '&text=' + encodeURIComponent(stream.title) +
                '&dates=' + this.formatDateForCalendar(stream.start_time) + '/' + this.formatDateForCalendar(stream.end_time) +
                '&details=' + encodeURIComponent(stream.description || '') +
                '&location=' + encodeURIComponent('Twitch.tv/' + stream.channel);

            window.open(calendarUrl, '_blank');
        },

        applyFilters: function() {
            this.loadStreams();
        },

        clearFilters: function() {
            $('.twitch-date-filter').val('');
            $('.twitch-status-filter').val('');
            $('.twitch-category-filter').val('');
            this.loadStreams();
        },

        showBulkActions: function() {
            // Implement bulk actions
            var selectedStreams = this.getSelectedStreams();
            if (selectedStreams.length === 0) {
                this.showMessage('Please select streams first', 'error');
                return;
            }

            // Show bulk actions modal
            this.showBulkActionsModal(selectedStreams);
        },

        getSelectedStreams: function() {
            // Get selected streams from checkboxes
            var selectedIds = [];
            $('.twitch-stream-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            return selectedIds;
        },

        showBulkActionsModal: function(streamIds) {
            var modalHtml = '<div class="twitch-scheduler-modal">' +
                '<div class="twitch-modal-content">' +
                '<div class="twitch-modal-header">' +
                '<h3 class="twitch-modal-title">Bulk Actions</h3>' +
                '<button class="twitch-modal-close">&times;</button>' +
                '</div>' +
                '<div class="twitch-modal-body">' +
                '<p>Apply action to ' + streamIds.length + ' selected streams:</p>' +
                '<select class="twitch-bulk-action-select">' +
                '<option value="status_scheduled">Set Status: Scheduled</option>' +
                '<option value="status_cancelled">Set Status: Cancelled</option>' +
                '<option value="delete">Delete Streams</option>' +
                '</select>' +
                '</div>' +
                '<div class="twitch-modal-actions">' +
                '<button class="twitch-modal-cancel">Cancel</button>' +
                '<button class="twitch-modal-apply">Apply</button>' +
                '</div>' +
                '</div>' +
                '</div>';

            $('body').append(modalHtml);
        },

        // Utility methods
        formatDate: function(dateString, format = 'Y-m-d') {
            var date = new Date(dateString);
            return date.toISOString().split('T')[0];
        },

        formatDateTime: function(dateTime) {
            if (typeof dateTime === 'string') {
                dateTime = new Date(dateTime);
            }
            return dateTime.toISOString().slice(0, 16);
        },

        formatDateForCalendar: function(dateString) {
            var date = new Date(dateString);
            return date.toISOString().replace(/[:-]/g, '').split('.')[0] + 'Z';
        },

        renderListItem: function(stream) {
            var categoryInfo = twitchStreamScheduler.categories[stream.category] || { name: 'General' };
            var statusClass = 'twitch-status-' + stream.status;

            return '<div class="twitch-stream-list-item ' + statusClass + '" data-stream-id="' + stream.id + '">' +
                '<div class="twitch-stream-time">' +
                '<div class="twitch-stream-date">' + this.formatDate(stream.start_time, 'M j') + '</div>' +
                '<div class="twitch-stream-clock">' + this.formatDate(stream.start_time, 'g:i A') + '</div>' +
                '</div>' +
                '<div class="twitch-stream-info">' +
                '<h4 class="twitch-stream-title">' + stream.title + '</h4>' +
                '<div class="twitch-stream-meta">' +
                '<span class="twitch-stream-channel">' + stream.channel + '</span>' +
                '<span class="twitch-stream-category">' + categoryInfo.name + '</span>' +
                '<span class="twitch-stream-status">' + stream.status + '</span>' +
                '</div>' +
                '</div>' +
                '</div>';
        },

        renderTimelineItem: function(stream) {
            var categoryInfo = twitchStreamScheduler.categories[stream.category] || { name: 'General' };

            return '<div class="twitch-timeline-item">' +
                '<div class="twitch-timeline-time">' +
                '<div class="twitch-time-date">' + this.formatDate(stream.start_time, 'M j') + '</div>' +
                '<div class="twitch-time-clock">' + this.formatDate(stream.start_time, 'g:i A') + '</div>' +
                '</div>' +
                '<div class="twitch-timeline-dot twitch-status-' + stream.status + '"></div>' +
                '<div class="twitch-timeline-content">' +
                '<h4 class="twitch-timeline-title">' + stream.title + '</h4>' +
                '<p class="twitch-timeline-channel">' + stream.channel + '</p>' +
                '<p class="twitch-timeline-category">' + categoryInfo.name + '</p>' +
                '</div>' +
                '</div>';
        },

        showModal: function(content) {
            this.hideModal();
            $('body').append(content);
        },

        hideModal: function() {
            $('.twitch-scheduler-modal').remove();
        },

        showLoading: function(show) {
            var $scheduler = $('.twitch-stream-scheduler');
            if (show) {
                $scheduler.addClass('twitch-loading');
            } else {
                $scheduler.removeClass('twitch-loading');
            }
        },

        showCalendarLoading: function() {
            $('.twitch-calendar-view').append('<div class="twitch-loading-overlay"><div class="twitch-spinner"></div></div>');
        },

        hideCalendarLoading: function() {
            $('.twitch-loading-overlay').remove();
        },

        showModalLoading: function(show) {
            var $modal = $('.twitch-modal-content');
            if (show) {
                $modal.addClass('twitch-loading');
            } else {
                $modal.removeClass('twitch-loading');
            }
        },

        showMessage: function(message, type) {
            var $message = $('<div class="twitch-scheduler-message ' + type + '">' + message + '</div>');
            $('body').append($message);

            setTimeout(function() {
                $message.fadeOut(function() {
                    $message.remove();
                });
            }, 3000);
        },

        showError: function(message) {
            this.showMessage(message, 'error');
        },

        initModals: function() {
            // Modal handling is done in bindEvents
        },

        initDragAndDrop: function() {
            // Initialize drag and drop for streams
            if (this.calendar) {
                // FullCalendar handles drag and drop internally
            }
        },

        loadStats: function() {
            var self = this;
            var channel = $('.twitch-stream-scheduler').data('channel');

            $.ajax({
                url: twitchStreamScheduler.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_stream_scheduler',
                    scheduler_action: 'get_stats',
                    channel: channel,
                    period: 'month',
                    nonce: twitchStreamScheduler.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.updateStatsDisplay(response.data);
                    }
                }
            });
        },

        updateStatsDisplay: function(stats) {
            $('.twitch-stat-number').each(function() {
                var statType = $(this).data('stat');
                if (stats[statType] !== undefined) {
                    $(this).text(stats[statType]);
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.twitch-stream-scheduler').length) {
            TwitchStreamScheduler.init();
        }
    });

    // Expose globally
    window.TwitchStreamScheduler = TwitchStreamScheduler;

})(jQuery);

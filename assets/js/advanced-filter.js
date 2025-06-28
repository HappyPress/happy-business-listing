/**
 * Advanced Filter JavaScript
 */
(function($) {
    'use strict';
    
    // Initialize the advanced filter
    function initAdvancedFilter() {
        // Initialize datepickers if they exist
        if ($.fn.datepicker) {
            $('.hbl-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-10:+10'
            });
        }
        
        // Initialize location search with geocoding
        initLocationSearch();
        
        // Initialize saved filters
        if ($('.hbl-advanced-filter').data('saved-filters') === true) {
            initSavedFilters();
        }
        
        // Handle mobile filter toggle
        $('.hbl-filter-toggle').on('click', function() {
            $('.hbl-advanced-filter-form').toggleClass('hbl-show-filters');
            
            var $toggleText = $(this).find('.hbl-filter-toggle-text');
            if ($('.hbl-advanced-filter-form').hasClass('hbl-show-filters')) {
                $toggleText.text(hblAdvancedFilter.i18n.hideFilters || 'Hide Filters');
            } else {
                $toggleText.text(hblAdvancedFilter.i18n.showFilters || 'Show Filters');
            }
        });
        
        // Handle form submission
        $('.hbl-advanced-filter-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var resultsContainer = $('#hbl-filter-results');
            
            // Show loading state
            resultsContainer.addClass('hbl-loading');
            resultsContainer.html('<div class="hbl-loading"><span class="hbl-loading-spinner"></span><span class="hbl-loading-text">Loading results...</span></div>');
            
            // Get form data
            var formData = form.serializeArray();
            var data = {
                action: 'hbl_advanced_filter_results',
                nonce: hblAdvancedFilter.nonce
            };
            
            // Convert form data to object
            $.each(formData, function(index, field) {
                data[field.name] = field.value;
            });
            
            // Send AJAX request
            $.ajax({
                url: hblAdvancedFilter.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    resultsContainer.removeClass('hbl-loading');
                    
                    if (response.success) {
                        resultsContainer.html(response.data.html);
                        
                        // Update URL with filter parameters
                        updateURL(formData);
                        
                        // Initialize pagination
                        initPagination();
                    } else {
                        resultsContainer.html('<div class="hbl-error">Error: ' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    resultsContainer.removeClass('hbl-loading');
                    resultsContainer.html('<div class="hbl-error">Error: Failed to load results. Please try again.</div>');
                }
            });
        });
        
        // Handle reset button
        $('.hbl-reset-button').on('click', function(e) {
            e.preventDefault();
            
            var form = $(this).closest('form');
            
            // Reset form fields
            form.find('input[type="text"], select').val('');
            form.find('input[type="checkbox"]').prop('checked', false);
            form.find('input[type="hidden"]').val('');
            
            // Submit the form
            form.submit();
        });
        
        // Auto-submit on select change if enabled
        if ($('.hbl-advanced-filter').data('auto-submit') === true) {
            $('.hbl-advanced-filter-form select, .hbl-advanced-filter-form input[type="checkbox"]').on('change', function() {
                $(this).closest('form').submit();
            });
        }
        
        // Save filter button
        $('.hbl-save-filter-button').on('click', function(e) {
            e.preventDefault();
            saveCurrentFilter();
        });
        
        // Initialize pagination
        initPagination();
        
        // Trigger initial load if there are filter parameters in URL
        if (window.location.search.indexOf('?') !== -1) {
            $('.hbl-advanced-filter-form').submit();
        }
    }
    
    // Initialize location search with geocoding
    function initLocationSearch() {
        var $locationSearch = $('.hbl-location-search');
        
        if ($locationSearch.length === 0) {
            return;
        }
        
        // Check if browser supports geolocation
        if (navigator.geolocation) {
            // Add "Use my location" button
            var $useMyLocation = $('<button type="button" class="hbl-use-my-location">' + (hblAdvancedFilter.i18n.useMyLocation || 'Use my location') + '</button>');
            $locationSearch.after($useMyLocation);
            
            // Handle "Use my location" click
            $useMyLocation.on('click', function(e) {
                e.preventDefault();
                
                // Show loading indicator
                $useMyLocation.addClass('hbl-loading');
                
                // Get current position
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    
                    // Set coordinates in hidden fields
                    $('#hbl-filter-latitude').val(lat);
                    $('#hbl-filter-longitude').val(lng);
                    
                    // Reverse geocode to get address
                    reverseGeocode(lat, lng, function(address) {
                        $locationSearch.val(address);
                        $useMyLocation.removeClass('hbl-loading');
                        
                        // Auto-submit if enabled
                        if ($('.hbl-advanced-filter').data('auto-submit') === true) {
                            $('.hbl-advanced-filter-form').submit();
                        }
                    });
                }, function(error) {
                    $useMyLocation.removeClass('hbl-loading');
                    alert('Error getting your location: ' + error.message);
                });
            });
        }
        
        // Handle location search input
        var geocodeTimeout;
        $locationSearch.on('input', function() {
            var query = $(this).val();
            
            // Clear previous timeout
            clearTimeout(geocodeTimeout);
            
            // Set new timeout to prevent too many requests
            if (query.length > 2) {
                geocodeTimeout = setTimeout(function() {
                    geocode(query, function(lat, lng) {
                        $('#hbl-filter-latitude').val(lat);
                        $('#hbl-filter-longitude').val(lng);
                    });
                }, 500);
            }
        });
    }
    
    // Geocode address to coordinates using Nominatim (OpenStreetMap)
    function geocode(address, callback) {
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            type: 'GET',
            data: {
                q: address,
                format: 'json',
                limit: 1
            },
            success: function(data) {
                if (data && data.length > 0) {
                    callback(data[0].lat, data[0].lon);
                }
            }
        });
    }
    
    // Reverse geocode coordinates to address
    function reverseGeocode(lat, lng, callback) {
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/reverse',
            type: 'GET',
            data: {
                lat: lat,
                lon: lng,
                format: 'json'
            },
            success: function(data) {
                if (data && data.display_name) {
                    callback(data.display_name);
                } else {
                    callback('Unknown location');
                }
            },
            error: function() {
                callback('Unknown location');
            }
        });
    }
    
    // Initialize saved filters
    function initSavedFilters() {
        // Load saved filters
        loadSavedFilters();
        
        // Handle saved filter click
        $(document).on('click', '.hbl-saved-filter-item', function(e) {
            e.preventDefault();
            
            var filterData = $(this).data('filter');
            if (filterData) {
                applySavedFilter(filterData);
            }
        });
        
        // Handle delete saved filter
        $(document).on('click', '.hbl-delete-saved-filter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var filterName = $(this).closest('.hbl-saved-filter-item').data('name');
            if (filterName) {
                deleteSavedFilter(filterName);
            }
        });
    }
    
    // Load saved filters from localStorage
    function loadSavedFilters() {
        var $savedFiltersList = $('.hbl-saved-filters-list');
        if ($savedFiltersList.length === 0) {
            return;
        }
        
        // Clear current list
        $savedFiltersList.empty();
        
        // Get saved filters from localStorage
        var savedFilters = getSavedFilters();
        
        // If no saved filters, show message
        if (Object.keys(savedFilters).length === 0) {
            $savedFiltersList.html('<p class="hbl-no-saved-filters">' + (hblAdvancedFilter.i18n.noSavedFilters || 'No saved filters yet.') + '</p>');
            return;
        }
        
        // Add each saved filter to the list
        $.each(savedFilters, function(name, data) {
            var $item = $('<div class="hbl-saved-filter-item" data-name="' + name + '"></div>');
            $item.data('filter', data);
            
            var $name = $('<span class="hbl-saved-filter-name">' + name + '</span>');
            var $delete = $('<button type="button" class="hbl-delete-saved-filter">&times;</button>');
            
            $item.append($name).append($delete);
            $savedFiltersList.append($item);
        });
    }
    
    // Save current filter to localStorage
    function saveCurrentFilter() {
        var form = $('.hbl-advanced-filter-form');
        var formData = form.serializeArray();
        
        // Convert form data to object
        var filterData = {};
        $.each(formData, function(index, field) {
            filterData[field.name] = field.value;
        });
        
        // Prompt for filter name
        var filterName = prompt(hblAdvancedFilter.i18n.saveFilterPrompt || 'Enter a name for this filter:');
        
        // If name provided, save the filter
        if (filterName) {
            var savedFilters = getSavedFilters();
            savedFilters[filterName] = filterData;
            
            // Save to localStorage
            localStorage.setItem('hbl_saved_filters', JSON.stringify(savedFilters));
            
            // Reload saved filters list
            loadSavedFilters();
        }
    }
    
    // Apply a saved filter
    function applySavedFilter(filterData) {
        var form = $('.hbl-advanced-filter-form');
        
        // Reset form first
        form.find('input[type="text"], select').val('');
        form.find('input[type="checkbox"]').prop('checked', false);
        
        // Apply filter data to form
        $.each(filterData, function(name, value) {
            var field = form.find('[name="' + name + '"]');
            
            if (field.is('input[type="checkbox"]')) {
                field.prop('checked', value === '1');
            } else {
                field.val(value);
            }
        });
        
        // Submit the form
        form.submit();
    }
    
    // Delete a saved filter
    function deleteSavedFilter(filterName) {
        if (confirm(hblAdvancedFilter.i18n.deleteFilterConfirm || 'Are you sure you want to delete this saved filter?')) {
            var savedFilters = getSavedFilters();
            
            // Remove the filter
            delete savedFilters[filterName];
            
            // Save to localStorage
            localStorage.setItem('hbl_saved_filters', JSON.stringify(savedFilters));
            
            // Reload saved filters list
            loadSavedFilters();
        }
    }
    
    // Get saved filters from localStorage
    function getSavedFilters() {
        var savedFilters = localStorage.getItem('hbl_saved_filters');
        return savedFilters ? JSON.parse(savedFilters) : {};
    }
    
    // Initialize pagination
    function initPagination() {
        $('.hbl-pagination a').on('click', function(e) {
            e.preventDefault();
            
            var page = getParameterByName('paged', $(this).attr('href')) || 1;
            var form = $('.hbl-advanced-filter-form');
            
            // Add page to form data
            $('<input>').attr({
                type: 'hidden',
                name: 'paged',
                value: page
            }).appendTo(form);
            
            // Submit the form
            form.submit();
            
            // Remove the temporary input
            form.find('input[name="paged"]').remove();
            
            // Scroll to results
            $('html, body').animate({
                scrollTop: $('.hbl-advanced-filter').offset().top - 50
            }, 500);
        });
    }
    
    // Update URL with filter parameters
    function updateURL(formData) {
        if (history.pushState) {
            var url = window.location.protocol + "//" + window.location.host + window.location.pathname;
            var params = [];
            
            $.each(formData, function(index, field) {
                if (field.value) {
                    params.push(encodeURIComponent(field.name) + '=' + encodeURIComponent(field.value));
                }
            });
            
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            window.history.pushState({path: url}, '', url);
        }
    }
    
    // Helper function to get URL parameter
    function getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        initAdvancedFilter();
    });
    
})(jQuery); 
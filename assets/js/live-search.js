/**
 * Live Search for Happy Business Listing
 * 
 * Provides real-time search functionality for business listings
 */
(function($) {
    'use strict';
    
    // Initialize once DOM is fully loaded
    $(document).ready(function() {
        const searchInput = $('.hbl-search-box input');
        let searchTimeout;
        let resultsContainer;
        
        // Create results container if it doesn't exist
        if ($('.hbl-live-search-results').length === 0) {
            searchInput.after('<div class="hbl-live-search-results"></div>');
        }
        
        resultsContainer = $('.hbl-live-search-results');
        
        // Add event listener for search input
        searchInput.on('keyup', function() {
            const searchTerm = $(this).val().trim();
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Hide results if search term is empty
            if (searchTerm.length < 3) {
                resultsContainer.hide();
                return;
            }
            
            // Set timeout to prevent too many requests
            searchTimeout = setTimeout(function() {
                performSearch(searchTerm);
            }, 500);
        });
        
        // Hide results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.hbl-search-box, .hbl-live-search-results').length) {
                resultsContainer.hide();
            }
        });
        
        // Perform AJAX search
        function performSearch(searchTerm) {
            $.ajax({
                url: hbl_search.ajax_url,
                type: 'POST',
                data: {
                    action: 'hbl_live_search',
                    security: hbl_search.security,
                    search: searchTerm
                },
                beforeSend: function() {
                    resultsContainer.html('<div class="hbl-searching">Searching...</div>');
                    resultsContainer.show();
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        displayResults(response.data);
                    } else {
                        resultsContainer.html('<div class="hbl-no-live-results">No results found</div>');
                    }
                },
                error: function() {
                    resultsContainer.html('<div class="hbl-search-error">Error performing search</div>');
                }
            });
        }
        
        // Display search results
        function displayResults(results) {
            let html = '<ul class="hbl-live-results-list">';
            
            results.forEach(function(result) {
                html += '<li class="hbl-live-result-item">';
                html += '<a href="' + result.permalink + '" class="hbl-live-result-link">';
                
                if (result.thumbnail) {
                    html += '<div class="hbl-live-result-image"><img src="' + result.thumbnail + '" alt="' + result.title + '"></div>';
                } else {
                    html += '<div class="hbl-live-result-image"><div class="hbl-live-result-placeholder"></div></div>';
                }
                
                html += '<div class="hbl-live-result-content">';
                html += '<h4 class="hbl-live-result-title">' + result.title + '</h4>';
                
                if (result.company_type || result.location) {
                    html += '<div class="hbl-live-result-meta">';
                    
                    if (result.company_type) {
                        html += '<span class="hbl-live-result-type">' + result.company_type + '</span>';
                    }
                    
                    if (result.location) {
                        html += '<span class="hbl-live-result-location">' + result.location + '</span>';
                    }
                    
                    html += '</div>';
                }
                
                html += '</div>'; // End content
                html += '</a>';
                html += '</li>';
            });
            
            html += '</ul>';
            html += '<div class="hbl-view-all"><a href="?search=' + searchInput.val() + '">View all results</a></div>';
            
            resultsContainer.html(html);
            
            // Add CSS for live search results
            addLiveSearchStyles();
        }
        
        // Add CSS styles for live search
        function addLiveSearchStyles() {
            if ($('#hbl-live-search-styles').length === 0) {
                const styles = `
                    <style id="hbl-live-search-styles">
                        .hbl-search-box {
                            position: relative;
                        }
                        
                        .hbl-live-search-results {
                            position: absolute;
                            top: 100%;
                            left: 0;
                            right: 0;
                            z-index: 1000;
                            background-color: white;
                            border-radius: 4px;
                            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                            margin-top: 5px;
                            display: none;
                            max-height: 400px;
                            overflow-y: auto;
                        }
                        
                        .hbl-searching,
                        .hbl-no-live-results,
                        .hbl-search-error {
                            padding: 15px;
                            text-align: center;
                        }
                        
                        .hbl-search-error {
                            color: #d63638;
                        }
                        
                        .hbl-live-results-list {
                            list-style: none;
                            margin: 0;
                            padding: 0;
                        }
                        
                        .hbl-live-result-item {
                            border-bottom: 1px solid #f0f0f0;
                        }
                        
                        .hbl-live-result-item:last-child {
                            border-bottom: none;
                        }
                        
                        .hbl-live-result-link {
                            display: flex;
                            padding: 10px;
                            text-decoration: none;
                            color: inherit;
                            transition: background-color 0.2s;
                        }
                        
                        .hbl-live-result-link:hover {
                            background-color: #f9f9f9;
                        }
                        
                        .hbl-live-result-image {
                            width: 50px;
                            height: 50px;
                            margin-right: 10px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        
                        .hbl-live-result-image img {
                            max-width: 100%;
                            max-height: 100%;
                            object-fit: contain;
                        }
                        
                        .hbl-live-result-placeholder {
                            width: 40px;
                            height: 40px;
                            background-color: #e0e0e0;
                            border-radius: 50%;
                        }
                        
                        .hbl-live-result-content {
                            flex: 1;
                        }
                        
                        .hbl-live-result-title {
                            margin: 0 0 5px 0;
                            font-size: 16px;
                        }
                        
                        .hbl-live-result-meta {
                            display: flex;
                            gap: 5px;
                            font-size: 12px;
                        }
                        
                        .hbl-live-result-type,
                        .hbl-live-result-location {
                            background-color: #f0f0f0;
                            padding: 2px 6px;
                            border-radius: 3px;
                        }
                        
                        .hbl-view-all {
                            padding: 10px;
                            text-align: center;
                            border-top: 1px solid #f0f0f0;
                        }
                        
                        .hbl-view-all a {
                            color: #2271b1;
                            text-decoration: none;
                            font-weight: bold;
                        }
                    </style>
                `;
                
                $('head').append(styles);
            }
        }
    });
})(jQuery);
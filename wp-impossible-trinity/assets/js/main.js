/**
 * Impossible Trinity Plugin - Main JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // View switching
    window.switchView = function(view) {
        const url = new URL(window.location);
        url.searchParams.set('view', view);
        url.searchParams.delete('paged');
        window.location.href = url.toString();
    };
    
    // Infinite scroll / Load more
    initInfiniteScroll();
    
    // Agree button functionality
    initAgreeButtons();
    
    // Filter functionality
    initFilters();
    
    /**
     * Initialize infinite scroll
     */
    function initInfiniteScroll() {
        const sentinel = document.getElementById('it-sentinel');
        const loader = document.getElementById('it-loader');
        
        if (!sentinel) return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadMoreItems();
                }
            });
        }, {
            rootMargin: '100px'
        });
        
        observer.observe(sentinel);
        
        /**
         * Load more items via AJAX
         */
        function loadMoreItems() {
            const gridId = $('#it-card-grid').length > 0 ? 'it-card-grid' : 'it-table-container';
            const container = $('#' + gridId);
            const view = container.data('view') || 'card';
            let page = container.data('page') || 1;
            const perPage = container.data('per-page') || 12;
            const field = container.data('field') || '';
            const search = container.data('search') || '';
            
            // Show loader
            if (loader) loader.style.display = 'block';
            
            $.ajax({
                url: wpit_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpit_load_more',
                    nonce: wpit_ajax.nonce,
                    page: page + 1,
                    view: view,
                    field: field,
                    search: search
                },
                success: function(response) {
                    if (response.success && response.html) {
                        // Append new items
                        if (view === 'table') {
                            $('#it-table-body').append(response.html);
                        } else {
                            $('#it-card-grid').append(response.html);
                        }
                        
                        // Update page number
                        container.data('page', page + 1);
                        
                        // Check if has more items
                        if (!response.has_more) {
                            observer.unobserve(sentinel);
                            if (loader) loader.style.display = 'none';
                        }
                    }
                    
                    // Hide loader
                    if (loader) loader.style.display = 'none';
                },
                error: function() {
                    if (loader) loader.style.display = 'none';
                }
            });
        }
    }
    
    /**
     * Initialize agree buttons
     */
    function initAgreeButtons() {
        $(document).on('click', '.it-agree-button', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const id = $button.data('id');
            const $countSpan = $button.find('.it-agree-count');
            
            $.ajax({
                url: wpit_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpit_agree',
                    nonce: wpit_ajax.nonce,
                    id: id
                },
                beforeSend: function() {
                    $button.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        // Update count
                        $countSpan.text('(' + response.data.count + ')');
                        
                        // Show success message
                        if (response.data.message) {
                            $button.append('<span class="it-success-msg"> ✓</span>');
                            setTimeout(function() {
                                $button.find('.it-success-msg').fadeOut(function() {
                                    $(this).remove();
                                });
                            }, 2000);
                        }
                    } else {
                        // Show error message
                        alert(response.data.message || 'An error occurred.');
                    }
                    $button.prop('disabled', false);
                },
                error: function() {
                    $button.prop('disabled', false);
                    alert('An error occurred. Please try again.');
                }
            });
        });
    }
    
    /**
     * Initialize filters
     */
    function initFilters() {
        // Field filter
        $(document).on('click', '.field-chip', function(e) {
            e.preventDefault();
            const field = $(this).data('field');
            filterByField(field);
        });
    }
    
    /**
     * Filter by field
     */
    window.filterByField = function(field) {
        const container = $('#it-card-grid').length > 0 ? $('#it-card-grid') : $('#it-table-container');
        const view = container.data('view') || 'card';
        
        $.ajax({
            url: wpit_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpit_filter',
                nonce: wpit_ajax.nonce,
                field: field,
                view: view
            },
            success: function(response) {
                if (response.success) {
                    if (view === 'table') {
                        $('#it-table-body').html(response.html);
                    } else {
                        $('#it-card-grid').html(response.html);
                    }
                    
                    // Update container data
                    container.data('page', 1);
                    container.data('field', field);
                    
                    // Re-init infinite scroll if has more items
                    if (response.has_more) {
                        initInfiniteScroll();
                    }
                    
                    // Update active field chip
                    $('.field-chip').removeClass('active');
                    if (field) {
                        $('.field-chip[data-field="' + field + '"]').addClass('active');
                    } else {
                        $('.field-chip[data-field=""]').addClass('active');
                    }
                }
            }
        });
    };
});
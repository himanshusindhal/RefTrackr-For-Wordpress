/**
 * RefTrackr Admin JavaScript
 *
 * Handles dashboard interactivity, chart rendering, AJAX operations,
 * influencer management, filtering, and settings.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

(function($) {
    'use strict';

    var RefTrackr = {

        /* -----------------------------------------------------------
         * Initialization
         * --------------------------------------------------------- */

        init: function() {
            this.page = this.detectPage();
            this.bindGlobalEvents();

            switch (this.page) {
                case 'dashboard':
                    this.initDashboard();
                    break;
                case 'influencers':
                    this.initInfluencers();
                    break;
                case 'influencer-form':
                    this.initInfluencerForm();
                    break;
                case 'orders':
                    this.initOrders();
                    break;
                case 'referrals':
                    this.initReferrals();
                    break;
                case 'settings':
                    this.initSettings();
                    break;
                case 'reports':
                    this.initReports();
                    break;
            }
        },

        detectPage: function() {
            var params = new URLSearchParams(window.location.search);
            var page = params.get('page') || '';
            var action = params.get('action') || '';

            if (page === 'reftrackr') return 'dashboard';
            if (page === 'reftrackr-influencers' && (action === 'add' || action === 'edit')) return 'influencer-form';
            if (page === 'reftrackr-influencers') return 'influencers';
            if (page === 'reftrackr-orders') return 'orders';
            if (page === 'reftrackr-referrals') return 'referrals';
            if (page === 'reftrackr-settings') return 'settings';
            if (page === 'reftrackr-reports') return 'reports';
            if (page === 'reftrackr-coupons') return 'coupons';
            return '';
        },

        bindGlobalEvents: function() {
            // Close notices on click
            $(document).on('click', '.reftrackr-notice', function() {
                $(this).fadeOut(200, function() { $(this).remove(); });
            });
        },

        /* -----------------------------------------------------------
         * Dashboard
         * --------------------------------------------------------- */

        initDashboard: function() {
            var self = this;
            self.activeMetric = 'revenue';

            // Date filter buttons
            $(document).on('click', '.reftrackr-date-btn', function() {
                var range = $(this).data('range');
                $('.reftrackr-date-btn').removeClass('active');
                $(this).addClass('active');

                if (range === 'custom') {
                    $('#reftrackr-custom-range').show();
                } else {
                    $('#reftrackr-custom-range').hide();
                    var dateTo = self.today();
                    var dateFrom = self.daysAgo(range);
                    self.loadDashboardData(dateFrom, dateTo);
                }
            });

            $('#reftrackr-apply-custom').on('click', function() {
                var from = $('#reftrackr-date-from').val();
                var to = $('#reftrackr-date-to').val();
                if (from && to) {
                    self.loadDashboardData(from, to);
                }
            });

            // Metric toggle select
            $('#reftrackr-chart-metric-select').on('change', function() {
                self.activeMetric = $(this).val();
                var canvas = document.getElementById('reftrackr-revenue-chart');
                if (canvas && canvas.chartData) {
                    self.renderChart(canvas, canvas.chartData);
                }
            });

            // Initial event listeners for chart hover tooltips
            var canvas = document.getElementById('reftrackr-revenue-chart');
            if (canvas) {
                canvas.addEventListener('mousemove', function(e) {
                    var rect = canvas.getBoundingClientRect();
                    var x = e.clientX - rect.left;
                    var y = e.clientY - rect.top;
                    self.renderChart(canvas, canvas.chartData, { x: x, y: y });
                });

                canvas.addEventListener('mouseleave', function() {
                    self.renderChart(canvas, canvas.chartData, null);
                });
            }

            // Load initial dashboard stats
            self.loadDashboardData(self.daysAgo(7), self.today());
        },

        loadDashboardData: function(dateFrom, dateTo) {
            var self = this;
            this.ajaxRequest('reftrackr_get_dashboard_stats', {
                date_from: dateFrom,
                date_to: dateTo
            }, function(response) {
                if (response.success && response.data) {
                    var d = response.data;
                    if (d.stats) {
                        self.animateValue($('#reftrackr-total-sales'), d.stats.total_revenue, true);
                        self.animateValue($('#reftrackr-total-orders'), d.stats.total_orders, false);
                        self.animateValue($('#reftrackr-active-influencers'), d.stats.active_influencers, false);
                    }
                    if (d.top_product && d.top_product.product_name) {
                        $('#reftrackr-top-product').text(d.top_product.product_name);
                        // Also update units sold trend label
                        var unitsLabel = d.top_product.units_sold + ' <small>units sold</small>';
                        $('.reftrackr-card-trend--units').html(unitsLabel);
                    } else {
                        $('#reftrackr-top-product').text('N/A');
                        $('.reftrackr-card-trend--units').html('0 <small>units sold</small>');
                    }
                    if (d.chart_data) {
                        var canvas = document.getElementById('reftrackr-revenue-chart');
                        if (canvas) {
                            canvas.chartData = d.chart_data;
                            self.renderChart(canvas, d.chart_data);
                        }

                        // Render sparklines inside analytics cards
                        var salesPoints = d.chart_data.map(function(item) { return item.revenue; });
                        var ordersPoints = d.chart_data.map(function(item) { return item.orders; });
                        var activeCount = d.stats ? d.stats.active_influencers : 0;
                        var activePoints = [
                            Math.round(activeCount * 0.7),
                            Math.round(activeCount * 0.8),
                            Math.round(activeCount * 0.75),
                            Math.round(activeCount * 0.9),
                            activeCount
                        ];
                        var productPoints = salesPoints.map(function(v) { return v * 0.35 + Math.random() * 10; });

                        self.renderSparkline('reftrackr-sparkline-sales', salesPoints, 'rgba(108, 92, 231, 1)');
                        self.renderSparkline('reftrackr-sparkline-orders', ordersPoints, 'rgba(9, 132, 227, 1)');
                        self.renderSparkline('reftrackr-sparkline-influencers', activePoints, 'rgba(46, 204, 113, 1)');
                        self.renderSparkline('reftrackr-sparkline-products', productPoints, 'rgba(230, 126, 34, 1)');
                    }
                }
            });
        },

        /* -----------------------------------------------------------
         * Canvas Chart Renderer
         * --------------------------------------------------------- */

        renderChart: function(canvas, data, mousePos) {
            if (!canvas) return;

            var ctx = canvas.getContext('2d');
            var dpr = window.devicePixelRatio || 1;
            var rect = canvas.parentElement.getBoundingClientRect();
            var width = rect.width;
            var height = 350;

            canvas.width = width * dpr;
            canvas.height = height * dpr;
            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';
            ctx.scale(dpr, dpr);

            // Clear
            ctx.clearRect(0, 0, width, height);

            // Margins
            var margin = { top: 40, right: 30, bottom: 50, left: 60 };
            var chartW = width - margin.left - margin.right;
            var chartH = height - margin.top - margin.bottom;

            if (!data || data.length === 0) {
                // Empty state
                ctx.fillStyle = '#B2BEC3';
                ctx.font = '14px -apple-system, BlinkMacSystemFont, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('No data available for the selected period', width / 2, height / 2);
                return;
            }

            var self = this;
            var metric = self.activeMetric || 'revenue';
            var metricValues = data.map(function(d) {
                return metric === 'revenue' ? d.revenue : d.orders;
            });
            var maxVal = Math.max.apply(null, metricValues) || 1;
            maxVal = maxVal * 1.25; // Add 25% headroom

            function yMetric(val) {
                return margin.top + chartH - (val / maxVal * chartH);
            }
            function xPos(i) {
                return margin.left + (i / (data.length - 1 || 1)) * chartW;
            }

            // Grid lines & Y-axis labels
            ctx.strokeStyle = '#F0F2F8';
            ctx.lineWidth = 1;
            var gridLines = 5;
            for (var g = 0; g <= gridLines; g++) {
                var gy = margin.top + (g / gridLines) * chartH;
                ctx.beginPath();
                ctx.moveTo(margin.left, gy);
                ctx.lineTo(margin.left + chartW, gy);
                ctx.stroke();

                var yVal = maxVal - (g / gridLines) * maxVal;
                ctx.fillStyle = '#B2BEC3';
                ctx.font = '11px -apple-system, BlinkMacSystemFont, sans-serif';
                ctx.textAlign = 'right';
                var formattedY = metric === 'revenue' ? self.formatCompact(yVal) : Math.round(yVal);
                ctx.fillText(formattedY, margin.left - 10, gy + 4);
            }

            // X-axis labels
            ctx.fillStyle = '#B2BEC3';
            ctx.font = '11px -apple-system, BlinkMacSystemFont, sans-serif';
            ctx.textAlign = 'center';
            var labelStep = Math.max(1, Math.floor(data.length / 7));
            for (var xi = 0; xi < data.length; xi += labelStep) {
                var xp = xPos(xi);
                var dateStr = data[xi].date;
                var parts = dateStr.split('-');
                var dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
                var label = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                ctx.fillText(label, xp, height - margin.bottom + 20);
            }

            // Area fill under line (Bezier spline path)
            ctx.beginPath();
            ctx.moveTo(xPos(0), yMetric(metricValues[0]));
            for (var ai = 1; ai < data.length; ai++) {
                var ax0 = xPos(ai - 1), ay0 = yMetric(metricValues[ai - 1]);
                var ax1 = xPos(ai), ay1 = yMetric(metricValues[ai]);
                var cpx = (ax0 + ax1) / 2;
                ctx.bezierCurveTo(cpx, ay0, cpx, ay1, ax1, ay1);
            }
            ctx.lineTo(xPos(data.length - 1), margin.top + chartH);
            ctx.lineTo(xPos(0), margin.top + chartH);
            ctx.closePath();

            var gradient = ctx.createLinearGradient(0, margin.top, 0, margin.top + chartH);
            gradient.addColorStop(0, 'rgba(108, 92, 231, 0.18)');
            gradient.addColorStop(1, 'rgba(108, 92, 231, 0.01)');
            ctx.fillStyle = gradient;
            ctx.fill();

            // Line stroke (Bezier spline path)
            ctx.beginPath();
            ctx.moveTo(xPos(0), yMetric(metricValues[0]));
            for (var li = 1; li < data.length; li++) {
                var lx0 = xPos(li - 1), ly0 = yMetric(metricValues[li - 1]);
                var lx1 = xPos(li), ly1 = yMetric(metricValues[li]);
                var lcpx = (lx0 + lx1) / 2;
                ctx.bezierCurveTo(lcpx, ly0, lcpx, ly1, lx1, ly1);
            }
            ctx.strokeStyle = '#6C5CE7';
            ctx.lineWidth = 3;
            ctx.stroke();

            // Data point circles
            for (var pi = 0; pi < data.length; pi++) {
                var px = xPos(pi), py = yMetric(metricValues[pi]);
                ctx.beginPath();
                ctx.arc(px, py, 4, 0, Math.PI * 2);
                ctx.fillStyle = '#6C5CE7';
                ctx.fill();
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 2;
                ctx.stroke();
            }

            // Hover interactions & Tooltip
            if (mousePos) {
                var activeIndex = -1;
                var minDist = 99999;
                for (var hi = 0; hi < data.length; hi++) {
                    var dist = Math.abs(xPos(hi) - mousePos.x);
                    if (dist < minDist) {
                        minDist = dist;
                        activeIndex = hi;
                    }
                }

                if (activeIndex !== -1 && minDist < 60) {
                    var hx = xPos(activeIndex);
                    var hy = yMetric(metricValues[activeIndex]);

                    // Vertical dashed guideline
                    ctx.strokeStyle = '#6C5CE7';
                    ctx.lineWidth = 1;
                    ctx.setLineDash([4, 4]);
                    ctx.beginPath();
                    ctx.moveTo(hx, margin.top);
                    ctx.lineTo(hx, margin.top + chartH);
                    ctx.stroke();
                    ctx.setLineDash([]); // Reset dash

                    // Highlight active point dot
                    ctx.beginPath();
                    ctx.arc(hx, hy, 8, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(108, 92, 231, 0.25)';
                    ctx.fill();
                    ctx.beginPath();
                    ctx.arc(hx, hy, 4, 0, Math.PI * 2);
                    ctx.fillStyle = '#6C5CE7';
                    ctx.fill();
                    ctx.strokeStyle = '#fff';
                    ctx.lineWidth = 2;
                    ctx.stroke();

                    // Render dark tooltip box
                    var tW = 160;
                    var tH = 50;
                    var tx = hx - tW / 2;
                    var ty = hy - tH - 12;

                    // Keep inside bounds
                    if (tx < margin.left) tx = margin.left;
                    if (tx + tW > width - margin.right) tx = width - margin.right - tW;
                    if (ty < 5) ty = hy + 12;

                    ctx.fillStyle = '#1e272e';
                    ctx.beginPath();
                    ctx.roundRect(tx, ty, tW, tH, 6);
                    ctx.fill();

                    // Tooltip text date
                    ctx.fillStyle = '#8f9ca6';
                    ctx.font = '10px -apple-system, BlinkMacSystemFont, sans-serif';
                    ctx.textAlign = 'center';
                    var dateParts = data[activeIndex].date.split('-');
                    var dObj = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                    var dStr = dObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    ctx.fillText(dStr, tx + tW / 2, ty + 18);

                    // Tooltip value text
                    ctx.textAlign = 'left';
                    var valueLabel = '';
                    if (metric === 'revenue') {
                        valueLabel = 'Total Sales: ' + self.formatCurrency(data[activeIndex].revenue);
                    } else {
                        valueLabel = 'Total Orders: ' + data[activeIndex].orders;
                    }

                    // Tooltip icon indicator dot
                    ctx.fillStyle = '#6C5CE7';
                    ctx.beginPath();
                    ctx.arc(tx + 18, ty + 34, 3.5, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 11px -apple-system, BlinkMacSystemFont, sans-serif';
                    ctx.fillText(valueLabel, tx + 28, ty + 38);
                }
            }
        },

        renderSparkline: function(canvasId, points, color) {
            var canvas = document.getElementById(canvasId);
            if (!canvas) return;

            var ctx = canvas.getContext('2d');
            var dpr = window.devicePixelRatio || 1;
            var w = canvas.clientWidth;
            var h = canvas.clientHeight;

            canvas.width = w * dpr;
            canvas.height = h * dpr;
            ctx.scale(dpr, dpr);

            ctx.clearRect(0, 0, w, h);

            if (!points || points.length < 2) {
                points = [10, 15, 8, 20, 18, 25, 22, 30, 28, 35];
            }

            var max = Math.max.apply(null, points) || 1;
            var min = Math.min.apply(null, points) || 0;
            var range = max - min || 1;

            function xPos(i) {
                return (i / (points.length - 1)) * w;
            }
            function yPos(val) {
                return h - 5 - ((val - min) / range) * (h - 10);
            }

            // Gradient fill
            ctx.beginPath();
            ctx.moveTo(xPos(0), yPos(points[0]));
            for (var i = 1; i < points.length; i++) {
                var x0 = xPos(i - 1), y0 = yPos(points[i - 1]);
                var x1 = xPos(i), y1 = yPos(points[i]);
                var cpx = (x0 + x1) / 2;
                ctx.bezierCurveTo(cpx, y0, cpx, y1, x1, y1);
            }
            ctx.lineTo(w, h);
            ctx.lineTo(0, h);
            ctx.closePath();

            var gradient = ctx.createLinearGradient(0, 0, 0, h);
            gradient.addColorStop(0, color.replace('1)', '0.12)'));
            gradient.addColorStop(1, color.replace('1)', '0.01)'));
            ctx.fillStyle = gradient;
            ctx.fill();

            // Stroke line
            ctx.beginPath();
            ctx.moveTo(xPos(0), yPos(points[0]));
            for (var j = 1; j < points.length; j++) {
                var sx0 = xPos(j - 1), sy0 = yPos(points[j - 1]);
                var sx1 = xPos(j), sy1 = yPos(points[j]);
                var scpx = (sx0 + sx1) / 2;
                ctx.bezierCurveTo(scpx, sy0, scpx, sy1, sx1, sy1);
            }
            ctx.strokeStyle = color;
            ctx.lineWidth = 2;
            ctx.stroke();
        },

        formatCompact: function(num) {
            var symbol = (typeof reftrackr_ajax !== 'undefined' && reftrackr_ajax.currency_symbol) ? reftrackr_ajax.currency_symbol : '$';
            if (num >= 1000000) return symbol + (num / 1000000).toFixed(1) + 'M';
            if (num >= 1000) return symbol + (num / 1000).toFixed(1) + 'K';
            return symbol + Math.round(num);
        },

        /* -----------------------------------------------------------
         * Influencer List
         * --------------------------------------------------------- */

        initInfluencers: function() {
            var self = this;

            // Search filter
            $('#reftrackr-search').on('input', function() {
                var term = $(this).val().toLowerCase();
                self.filterInfluencerTable();
            });

            // Status filter
            $('#reftrackr-status-filter').on('change', function() {
                self.filterInfluencerTable();
            });

            // Delete influencer
            $(document).on('click', '.reftrackr-delete-influencer', function() {
                var id = $(this).data('id');
                var row = $(this).closest('tr');

                if (!confirm(reftrackr_ajax.strings.confirm_delete)) return;

                self.ajaxRequest('reftrackr_delete_influencer', { id: id }, function(response) {
                    if (response.success) {
                        row.fadeOut(300, function() { $(this).remove(); });
                        self.showNotice(reftrackr_ajax.strings.deleted, 'success');
                    } else {
                        self.showNotice(response.data.message || reftrackr_ajax.strings.error, 'error');
                    }
                });
            });

            // Toggle status
            $(document).on('click', '.reftrackr-toggle-status', function() {
                var btn = $(this);
                var id = btn.data('id');

                self.ajaxRequest('reftrackr_toggle_influencer', { id: id }, function(response) {
                    if (response.success) {
                        var newStatus = response.data.status;
                        var badge = $('#reftrackr-status-' + id);
                        badge.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                        badge.removeClass('reftrackr-badge--active reftrackr-badge--paused')
                             .addClass('reftrackr-badge--' + newStatus);

                        // Update row data attribute
                        btn.closest('tr').attr('data-status', newStatus);

                        // Update button icon
                        var icon = btn.find('.dashicons');
                        if (newStatus === 'active') {
                            icon.removeClass('dashicons-controls-play').addClass('dashicons-controls-pause');
                            btn.attr('title', 'Pause');
                        } else {
                            icon.removeClass('dashicons-controls-pause').addClass('dashicons-controls-play');
                            btn.attr('title', 'Activate');
                        }

                        self.showNotice(reftrackr_ajax.strings.status_updated, 'success');
                    } else {
                        self.showNotice(response.data.message || reftrackr_ajax.strings.error, 'error');
                    }
                });
            });
        },

        filterInfluencerTable: function() {
            var search = ($('#reftrackr-search').val() || '').toLowerCase();
            var status = $('#reftrackr-status-filter').val();

            $('#reftrackr-influencers-table tbody tr').each(function() {
                var name = $(this).data('name') || '';
                var rowStatus = $(this).data('status') || '';
                var matchSearch = !search || name.indexOf(search) !== -1;
                var matchStatus = !status || rowStatus === status;

                $(this).toggle(matchSearch && matchStatus);
            });
        },

        /* -----------------------------------------------------------
         * Influencer Form
         * --------------------------------------------------------- */

        initInfluencerForm: function() {
            var self = this;

            // Auto-generate slug from name
            $('#reftrackr-name').on('input', function() {
                var slug = $('#reftrackr-slug');
                if (!slug.val() || slug.data('auto')) {
                    var generated = self.slugify($(this).val());
                    slug.val(generated);
                    slug.data('auto', true);
                    self.updateSlugPreview(generated);
                }
            });

            $('#reftrackr-slug').on('input', function() {
                $(this).data('auto', false);
                self.updateSlugPreview($(this).val());
            });

            // Form submission
            $('#reftrackr-influencer-form').on('submit', function(e) {
                e.preventDefault();

                var name = $('#reftrackr-name').val().trim();
                var slug = $('#reftrackr-slug').val().trim();

                if (!name) {
                    self.showNotice(reftrackr_ajax.strings.required_name, 'error', '#reftrackr-form-notice');
                    $('#reftrackr-name').focus();
                    return;
                }
                if (!slug) {
                    self.showNotice(reftrackr_ajax.strings.required_slug, 'error', '#reftrackr-form-notice');
                    $('#reftrackr-slug').focus();
                    return;
                }

                var isEdit = $('input[name="influencer_id"]').length > 0;
                var action = isEdit ? 'reftrackr_update_influencer' : 'reftrackr_add_influencer';
                var btn = $('#reftrackr-submit-btn');
                var originalText = btn.text();

                btn.prop('disabled', true).text(reftrackr_ajax.strings.saving);

                var formData = {
                    name: name,
                    email: $('#reftrackr-email').val(),
                    instagram_handle: $('#reftrackr-instagram').val(),
                    referral_slug: self.slugify(slug),
                    coupon_code: $('#reftrackr-coupon').val()
                };

                if (isEdit) {
                    formData.id = $('input[name="influencer_id"]').val();
                }

                self.ajaxRequest(action, formData, function(response) {
                    btn.prop('disabled', false).text(originalText);

                    if (response.success) {
                        var msg = isEdit ? reftrackr_ajax.strings.influencer_updated : reftrackr_ajax.strings.influencer_added;
                        self.showNotice(msg, 'success', '#reftrackr-form-notice');
                        setTimeout(function() {
                            window.location.href = reftrackr_ajax.admin_url + '?page=reftrackr-influencers';
                        }, 1000);
                    } else {
                        self.showNotice(response.data.message || reftrackr_ajax.strings.error, 'error', '#reftrackr-form-notice');
                    }
                });
            });
        },

        updateSlugPreview: function(slug) {
            var preview = $('#reftrackr-slug-preview');
            if (slug) {
                preview.text('Referral URL: ' + reftrackr_ajax.site_url + '?ref=' + this.slugify(slug));
            } else {
                preview.text('Auto-generated from name if left empty.');
            }
        },

        /* -----------------------------------------------------------
         * Orders Page
         * --------------------------------------------------------- */

        initOrders: function() {
            var self = this;
            var currentPage = 1;

            $('#reftrackr-apply-order-filters').on('click', function() {
                currentPage = 1;
                self.loadOrders(currentPage);
            });

            $('#reftrackr-reset-order-filters').on('click', function() {
                $('#reftrackr-filter-influencer').val('');
                $('#reftrackr-filter-date-from').val('');
                $('#reftrackr-filter-date-to').val('');
                $('#reftrackr-filter-coupon').val('');
                currentPage = 1;
                window.location.reload();
            });

            $(document).on('click', '.reftrackr-load-more[data-type="orders"]', function() {
                currentPage++;
                self.loadOrders(currentPage, true);
            });
        },

        loadOrders: function(page, append) {
            var self = this;
            var data = {
                influencer_id: $('#reftrackr-filter-influencer').val(),
                date_from: $('#reftrackr-filter-date-from').val(),
                date_to: $('#reftrackr-filter-date-to').val(),
                coupon: $('#reftrackr-filter-coupon').val(),
                page: page
            };

            self.ajaxRequest('reftrackr_get_orders', data, function(response) {
                if (response.success && response.data.orders) {
                    var html = '';
                    var currency = reftrackr_ajax.currency_symbol;

                    response.data.orders.forEach(function(order) {
                        var location = [order.city, order.state].filter(Boolean).join(', ') || '—';
                        var sourceBadge = order.referral_source ?
                            '<span class="reftrackr-badge reftrackr-badge--' + self.escapeHtml(order.referral_source) + '">' + self.escapeHtml(self.ucfirst(order.referral_source)) + '</span>' : '—';
                        var statusBadge = '<span class="reftrackr-badge reftrackr-badge--' + self.escapeHtml(order.order_status) + '">' + self.escapeHtml(self.ucfirst(order.order_status)) + '</span>';

                        html += '<tr>' +
                            '<td><a href="' + reftrackr_ajax.admin_url.replace('admin.php', 'post.php') + '?post=' + order.order_id + '&action=edit" target="_blank">#' + order.order_id + '</a></td>' +
                            '<td>' + self.escapeHtml(order.influencer_name || '—') + '</td>' +
                            '<td>' + self.escapeHtml(order.product_name || '—') + '</td>' +
                            '<td>' + self.escapeHtml(location) + '</td>' +
                            '<td>' + sourceBadge + '</td>' +
                            '<td>' + self.escapeHtml(order.coupon_used || '—') + '</td>' +
                            '<td>' + currency + self.formatNumber(order.order_total) + '</td>' +
                            '<td>' + statusBadge + '</td>' +
                            '<td>' + self.formatDate(order.created_at) + '</td>' +
                        '</tr>';
                    });

                    if (append) {
                        $('#reftrackr-orders-tbody').append(html);
                    } else {
                        $('#reftrackr-orders-tbody').html(html);
                    }

                    // Update pagination visibility
                    if (response.data.orders.length < 20) {
                        $('.reftrackr-load-more[data-type="orders"]').hide();
                    }
                }
            });
        },

        /* -----------------------------------------------------------
         * Referrals Page
         * --------------------------------------------------------- */

        initReferrals: function() {
            var self = this;
            var currentPage = 1;

            $('#reftrackr-apply-ref-filters').on('click', function() {
                currentPage = 1;
                self.loadReferrals(currentPage);
            });

            $('#reftrackr-reset-ref-filters').on('click', function() {
                $('#reftrackr-ref-influencer').val('');
                $('#reftrackr-ref-date-from').val('');
                $('#reftrackr-ref-date-to').val('');
                window.location.reload();
            });

            $(document).on('click', '.reftrackr-load-more[data-type="referrals"]', function() {
                currentPage++;
                self.loadReferrals(currentPage, true);
            });
        },

        loadReferrals: function(page, append) {
            var self = this;
            var data = {
                influencer_id: $('#reftrackr-ref-influencer').val(),
                date_from: $('#reftrackr-ref-date-from').val(),
                date_to: $('#reftrackr-ref-date-to').val(),
                page: page
            };

            self.ajaxRequest('reftrackr_get_referrals', data, function(response) {
                if (response.success && response.data.clicks) {
                    var html = '';
                    response.data.clicks.forEach(function(click) {
                        var deviceIcon = click.device_type === 'mobile' ? '📱' : (click.device_type === 'tablet' ? '📋' : '🖥️');
                        var referrer = click.referrer_url ? '<a href="' + self.escapeHtml(click.referrer_url) + '" target="_blank" rel="noopener">' + self.escapeHtml(self.getHostname(click.referrer_url)) + '</a>' : 'Direct';

                        html += '<tr>' +
                            '<td>' + self.escapeHtml(click.influencer_name || '—') + '</td>' +
                            '<td>' + deviceIcon + ' ' + self.escapeHtml(self.ucfirst(click.device_type)) + '</td>' +
                            '<td>' + referrer + '</td>' +
                            '<td>' + self.formatDate(click.clicked_at) + '</td>' +
                        '</tr>';
                    });

                    if (append) {
                        $('#reftrackr-clicks-tbody').append(html);
                    } else {
                        $('#reftrackr-clicks-tbody').html(html);
                    }

                    if (response.data.clicks.length < 20) {
                        $('.reftrackr-load-more[data-type="referrals"]').hide();
                    }
                }
            });
        },

        /* -----------------------------------------------------------
         * Settings Page
         * --------------------------------------------------------- */

        initSettings: function() {
            var self = this;

            // Cookie duration toggle
            $('#reftrackr-cookie-duration').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#reftrackr-custom-duration-wrap').slideDown(200);
                } else {
                    $('#reftrackr-custom-duration-wrap').slideUp(200);
                }
            });

            // Save settings
            $('#reftrackr-settings-form').on('submit', function(e) {
                e.preventDefault();

                var btn = $('#reftrackr-save-settings');
                var originalText = btn.text();
                btn.prop('disabled', true).text(reftrackr_ajax.strings.saving);

                var durationSelect = $('#reftrackr-cookie-duration').val();
                var cookieDuration = durationSelect === 'custom'
                    ? ($('#reftrackr-custom-duration').val() || 7)
                    : durationSelect;

                var data = {
                    cookie_duration: cookieDuration,
                    tracking_enabled: $('input[name="tracking_enabled"]').is(':checked') ? 'yes' : 'no',
                    coupon_attribution: $('input[name="coupon_attribution"]').is(':checked') ? 'yes' : 'no',
                    currency: $('input[name="currency"]').val(),
                    date_format: $('select[name="date_format"]').val()
                };

                self.ajaxRequest('reftrackr_save_settings', data, function(response) {
                    btn.prop('disabled', false).text(originalText);
                    if (response.success) {
                        self.showNotice(response.data.message || reftrackr_ajax.strings.saved, 'success', '#reftrackr-settings-notice');
                    } else {
                        self.showNotice(response.data.message || reftrackr_ajax.strings.error, 'error', '#reftrackr-settings-notice');
                    }
                });
            });
        },

        /* -----------------------------------------------------------
         * Reports Page
         * --------------------------------------------------------- */

        initReports: function() {
            var self = this;

            $('#reftrackr-apply-report-filters').on('click', function() {
                var from = $('#reftrackr-report-date-from').val();
                var to = $('#reftrackr-report-date-to').val();

                if (from || to) {
                    self.ajaxRequest('reftrackr_get_reports', {
                        date_from: from,
                        date_to: to,
                        type: 'all'
                    }, function(response) {
                        if (response.success) {
                            // Reload page with results (simplest approach for server-rendered tables)
                            window.location.reload();
                        }
                    });
                }
            });
        },

        /* -----------------------------------------------------------
         * Utility Functions
         * --------------------------------------------------------- */

        ajaxRequest: function(action, data, callback) {
            data = data || {};
            data.action = action;
            data.nonce = reftrackr_ajax.nonce;

            $.ajax({
                url: reftrackr_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (typeof callback === 'function') {
                        callback(response);
                    }
                },
                error: function() {
                    if (typeof callback === 'function') {
                        callback({ success: false, data: { message: reftrackr_ajax.strings.error } });
                    }
                }
            });
        },

        showNotice: function(message, type, container) {
            type = type || 'success';
            container = container || '.reftrackr-header';

            var icon = type === 'success' ? '✓' : '✕';
            var notice = $('<div class="reftrackr-notice reftrackr-notice--' + type + '">' +
                '<strong>' + icon + '</strong> ' + this.escapeHtml(message) +
            '</div>');

            $(container).after(notice);

            // Auto-dismiss
            setTimeout(function() {
                notice.fadeOut(300, function() { $(this).remove(); });
            }, 5000);
        },

        animateValue: function($el, endValue, isCurrency) {
            var currency = reftrackr_ajax.currency_symbol || '$';
            var start = 0;
            var duration = 600;
            var startTime = null;

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3); // ease-out
                var current = start + (endValue - start) * eased;

                if (isCurrency) {
                    $el.text(currency + parseFloat(current).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                } else {
                    $el.text(Math.round(current).toLocaleString());
                }

                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }

            requestAnimationFrame(step);
        },

        formatCurrency: function(amount) {
            var currency = reftrackr_ajax.currency_symbol || '$';
            return currency + parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        formatNumber: function(num) {
            return parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        formatDate: function(dateStr) {
            if (!dateStr) return '—';
            var d = new Date(dateStr);
            return d.toLocaleDateString();
        },

        slugify: function(text) {
            return (text || '')
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_]+/g, '-')
                .replace(/^-+|-+$/g, '');
        },

        escapeHtml: function(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        },

        ucfirst: function(str) {
            return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
        },

        getHostname: function(url) {
            try {
                return new URL(url).hostname;
            } catch (e) {
                return url;
            }
        },

        today: function() {
            return new Date().toISOString().split('T')[0];
        },

        daysAgo: function(days) {
            var d = new Date();
            d.setDate(d.getDate() - days);
            return d.toISOString().split('T')[0];
        }
    };

    // Polyfill roundRect for older browsers
    if (!CanvasRenderingContext2D.prototype.roundRect) {
        CanvasRenderingContext2D.prototype.roundRect = function(x, y, w, h, r) {
            if (w < 2 * r) r = w / 2;
            if (h < 2 * r) r = h / 2;
            this.moveTo(x + r, y);
            this.arcTo(x + w, y, x + w, y + h, r);
            this.arcTo(x + w, y + h, x, y + h, r);
            this.arcTo(x, y + h, x, y, r);
            this.arcTo(x, y, x + w, y, r);
            return this;
        };
    }

    // Chart resize handler
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            var canvas = document.getElementById('reftrackr-revenue-chart');
            if (canvas) {
                RefTrackr.renderChart(canvas, []);
            }
        }, 250);
    });

    $(document).ready(function() {
        RefTrackr.init();
    });

})(jQuery);

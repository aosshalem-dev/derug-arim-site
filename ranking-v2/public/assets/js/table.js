/**
 * Table Module
 * Handles table display and rendering
 */

(function() {
    'use strict';
    
    /**
     * Get domain from URL
     */
    function getDomainFromUrl(url) {
        if (!url) return '';
        try {
            const urlObj = new URL(url);
            const hostname = urlObj.hostname;
            // Remove 'www.' if present
            return hostname.replace(/^www\./, '') + '...';
        } catch (e) {
            return url.substring(0, 20) + '...';
        }
    }
    
    /**
     * Get relevance color
     */
    function getRelevanceColor(level) {
        const colors = {
            1: '#f44336', // Red
            2: '#ff9800', // Orange
            3: '#ffc107', // Yellow
            4: '#4CAF50', // Green
            5: '#2196F3'  // Blue
        };
        return colors[level] || '#666';
    }
    
    /**
     * Get label for enum value
     */
    function getLabel(value, type) {
        if (!value) return null;
        
        const labels = {
            topicCategory: {
                'education': 'חינוך',
                'culture': 'תרבות',
                'policy': 'מדיניות',
                'news': 'חדשות',
                'research': 'מחקר',
                'heritage': 'מורשת',
                'community': 'קהילה',
                'other': 'אחר'
            },
            organizationType: {
                'municipality': 'רשות מקומית',
                'government_agency': 'סוכנות ממשלתית',
                'media': 'תקשורת',
                'educational_institution': 'מוסד חינוכי',
                'ngo': 'עמותה',
                'research_institution': 'מוסד מחקר',
                'other': 'אחר'
            },
            sourceType: {
                'municipality': 'רשות מקומית',
                'government': 'ממשלתי',
                'media': 'תקשורת',
                'educational': 'חינוכי',
                'ngo': 'עמותה',
                'research': 'מחקר',
                'other': 'אחר'
            },
            documentType: {
                'report': 'דוח',
                'article': 'מאמר',
                'policy_document': 'מסמך מדיניות',
                'curriculum': 'תכנית לימודים',
                'announcement': 'הודעה',
                'protocol': 'פרוטוקול',
                'plan': 'תכנית',
                'other': 'אחר'
            },
            targetAudience: {
                'general_public': 'ציבור כללי',
                'educators': 'מחנכים',
                'students': 'תלמידים',
                'policymakers': 'קובעי מדיניות',
                'researchers': 'חוקרים',
                'community_leaders': 'מנהיגי קהילה',
                'other': 'אחר'
            },
            culturalFocus: {
                'hebrew_culture': 'תרבות עברית',
                'jewish_heritage': 'מורשת יהודית',
                'israeli_identity': 'זהות ישראלית',
                'multicultural': 'רב-תרבותי',
                'universal': 'אוניברסלי',
                'mixed': 'מעורב',
                'unclear': 'לא ברור'
            },
            zionismReferences: {
                'explicit': 'מפורש',
                'implicit': 'מרומז',
                'none': 'אין',
                'unclear': 'לא ברור'
            },
            language: {
                'hebrew': 'עברית',
                'english': 'אנגלית',
                'arabic': 'ערבית',
                'mixed': 'מעורב',
                'other': 'אחר'
            },
            accessibilityLevel: {
                'public': 'ציבורי',
                'restricted': 'מוגבל',
                'unclear': 'לא ברור'
            },
            publicationFormat: {
                'pdf': 'PDF',
                'html': 'HTML',
                'text': 'טקסט',
                'image': 'תמונה',
                'video': 'וידאו',
                'other': 'אחר'
            },
            jurisdictionLevel: {
                'local': 'מקומי',
                'regional': 'אזורי',
                'national': 'לאומי',
                'international': 'בינלאומי'
            }
        };
        
        return labels[type]?.[value] || value;
    }
    
    /**
     * Display records in table
     */
    function displayRecords(records) {
        console.log('=== DISPLAY RECORDS CALLED ===');
        console.log('Records count:', records.length);
        
        const tbody = document.getElementById('tableBody');
        
        if (!tbody) {
            console.error('ERROR: tableBody element not found!');
            return;
        }
        
        if (records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="no-data">לא נמצאו רשומות</td></tr>';
            return;
        }
        
        // Build table rows
        const rows = records.map((record, index) => {
            const hasSummary = record.short_summary && String(record.short_summary).trim().length > 0;
            const summaryText = hasSummary ? String(record.short_summary).trim() : '';
            const hasManualSummary = record.manual_summary && String(record.manual_summary).trim().length > 0;
            const manualSummaryText = hasManualSummary ? String(record.manual_summary).trim() : '';
            
            return `
        <tr>
            <td>${record.id}</td>
            <td class="url-cell" style="min-width: 200px; max-width: 250px;">
                <a href="${record.url}" target="_blank" title="${record.url}" style="word-break: break-all; white-space: normal;">
                    ${record.url ? record.url.replace(/^https?:\/\//, '').substring(0, 50) + (record.url.length > 60 ? '...' : '') : '-'}
                </a>
            </td>
            <td class="editable-organization-name" data-record-id="${record.id}" data-current-value="${record.organization_name || ''}" style="cursor: pointer; padding: 8px; min-width: 150px;" title="לחץ לעריכה">
                <span class="org-name-display">${record.organization_name || '-'}</span>
                <input type="text" class="org-name-edit" style="display: none; width: 100%; padding: 6px; border: 2px solid #667eea; border-radius: 4px; font-size: 14px;"
                       value="${record.organization_name || ''}"
                       placeholder="הקלד שם ארגון..."
                       onblur="window.InlineEdit.saveOrganizationNameInline(${record.id}, this.value)"
                       onkeydown="if(event.key === 'Enter') { event.preventDefault(); window.InlineEdit.saveOrganizationNameInline(${record.id}, this.value); }">
            </td>
            <td class="editable-organization-type" data-record-id="${record.id}" data-current-value="${record.organization_type || ''}" style="cursor: pointer; padding: 8px; min-width: 100px;" title="לחץ לעריכה">
                <span class="org-type-display">${record.organization_type || '-'}</span>
                <input type="text" class="org-type-edit" style="display: none; width: 100%; padding: 6px; border: 2px solid #667eea; border-radius: 4px; font-size: 14px;"
                       value="${record.organization_type || ''}"
                       placeholder="הקלד סוג ארגון..."
                       onblur="window.InlineEdit.saveOrganizationTypeInline(${record.id}, this.value)"
                       onkeydown="if(event.key === 'Enter') { event.preventDefault(); window.InlineEdit.saveOrganizationTypeInline(${record.id}, this.value); }">
            </td>
            <td class="editable-category" data-record-id="${record.id}" data-current-value="${record.topic_category || ''}" style="cursor: pointer; padding: 8px; min-width: 100px;" title="לחץ לעריכה">
                <span class="category-display">${record.topic_category || '-'}</span>
                <input type="text" class="category-edit" style="display: none; width: 100%; padding: 6px; border: 2px solid #667eea; border-radius: 4px; font-size: 14px;"
                       value="${record.topic_category || ''}"
                       placeholder="הקלד קטגוריה..."
                       onblur="window.InlineEdit.saveCategoryInline(${record.id}, this.value)"
                       onkeydown="if(event.key === 'Enter') { event.preventDefault(); window.InlineEdit.saveCategoryInline(${record.id}, this.value); }">
            </td>
            <td>${record.year || '-'}</td>
            <td class="editable-relevance" data-record-id="${record.id}" data-current-value="${record.relevance_level || ''}" style="cursor: pointer; padding: 8px; text-align: center; min-width: 60px;" title="לחץ לעריכה (1-5)">
                <span class="relevance-display">
                    ${record.relevance_level ?
                        `<span style="font-weight: bold; color: ${getRelevanceColor(record.relevance_level)}; font-size: 16px;">${record.relevance_level}</span>` :
                        '-'
                    }
                </span>
                <input type="number" class="relevance-edit" min="1" max="5" style="display: none; width: 60px; padding: 6px; border: 2px solid #667eea; border-radius: 4px; font-size: 16px; text-align: center;"
                       value="${record.relevance_level || ''}"
                       placeholder="1-5"
                       onblur="window.InlineEdit.saveRelevanceInline(${record.id}, this.value)"
                       onkeydown="if(event.key === 'Enter') { event.preventDefault(); window.InlineEdit.saveRelevanceInline(${record.id}, this.value); }">
            </td>
            <td style="text-align: center;">
                ${record.ai_relevance_score ? 
                    `<span style="font-weight: bold; color: ${getRelevanceColor(record.ai_relevance_score)};" title="${record.ai_relevance_reason || ''}">${record.ai_relevance_score}</span>` : 
                    '-'
                }
            </td>
            <td class="summary-cell" style="min-width: 300px; max-width: 400px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    ${hasManualSummary || hasSummary ?
                        `<div style="display: flex; flex-direction: column; gap: 8px;">
                            ${hasManualSummary ?
                                `<div class="summary-text-clickable" title="לחץ לצפייה בסיכום המלא" onclick="window.Modals.openViewSummaryModal(${record.id})" data-record-id="${record.id}" style="cursor: pointer; color: #4CAF50; line-height: 1.5;">
                                    <div style="font-weight: bold; margin-bottom: 4px; font-size: 12px;">📝 ידני:</div>
                                    <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; font-size: 12px;">${manualSummaryText}</div>
                                </div>` : ''
                            }
                            ${hasSummary ?
                                `<div class="summary-text-clickable" title="לחץ לצפייה בסיכום המלא" onclick="window.Modals.openViewSummaryModal(${record.id})" data-record-id="${record.id}" style="cursor: pointer; color: #2196F3; line-height: 1.5;">
                                    <div style="font-weight: bold; margin-bottom: 4px; font-size: 12px;">🤖 AI:</div>
                                    <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; font-size: 12px;">${summaryText}</div>
                                </div>` : ''
                            }
                        </div>` :
                        '<span style="color: #999;">אין סיכום</span>'
                    }
                    <button onclick="window.Modals.openRecordModal(${record.id})" title="ערוך את כל הפרמטרים" style="padding: 8px 16px; font-size: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 8px;">
                        ✏️ עריכה מלאה
                    </button>
                </div>
            </td>
            <td class="actions-cell">
                ${record.metadata_status === 'failed' ? `
                <button class="btn-icon btn-retry" onclick="window.App.retryExtraction(${record.id})" title="נסה שוב" id="retry-btn-${record.id}">
                    🔄
                </button>
                ` : ''}
                <button class="btn-icon btn-danger" onclick="window.App.deleteRecord(${record.id})" title="מחק">
                    🗑️
                </button>
            </td>
        </tr>
    `;
        });
        
        const htmlContent = rows.join('');
        tbody.innerHTML = htmlContent;
        
        console.log('=== DISPLAY RECORDS COMPLETED ===');
        console.log('Total records:', records.length);
        
        // Update displayed records count
        if (window.App && window.App.updateDisplayedCount) {
            window.App.updateDisplayedCount(records.length);
        }
        
        // Re-setup sortable headers after table update (in case headers were recreated)
        if (window.App && window.App.setupSortableHeaders) {
            window.App.setupSortableHeaders();
        }
    }
    
    /**
     * Apply filters (delegates to App module)
     */
    function applyFilters() {
        if (window.App && window.App.applyFilters) {
            window.App.applyFilters();
        } else if (window.applyFilters) {
            window.applyFilters();
        }
    }
    
    /**
     * Clear filters (delegates to App module)
     */
    function clearFilters() {
        if (window.App && window.App.clearFilters) {
            window.App.clearFilters();
        } else if (window.clearFilters) {
            window.clearFilters();
        }
    }
    
    // Export to global scope
    window.Table = {
        displayRecords,
        getDomainFromUrl,
        getRelevanceColor,
        getLabel,
        applyFilters,
        clearFilters
    };
    
    // Export lowercase alias for backward compatibility
    window.table = window.Table;
    
    // Also export utility functions globally for backward compatibility
    window.getDomainFromUrl = getDomainFromUrl;
    window.getRelevanceColor = getRelevanceColor;
    window.getLabel = getLabel;
})();



/**
 * Inline Editing Module
 * Handles inline editing for category and organization type cells
 */

(function() {
    'use strict';

    let initialized = false;
    let savingInProgress = {}; // Track saves in progress by recordId and field type
    
    /**
     * Initialize inline editing
     */
    function init() {
        if (initialized) return;
        initialized = true;
        
        // Use event delegation with capture phase to run before other handlers
        document.addEventListener('click', handleClick, true);
        
        console.log('Inline editing initialized');
    }
    
    /**
     * Handle click events for inline editing
     */
    function handleClick(e) {
        // Check for relevance cell FIRST (was having issues)
        const relevanceCell = findRelevanceCell(e.target);
        if (relevanceCell) {
            console.log('Relevance cell found:', relevanceCell.getAttribute('data-record-id'));
            if (shouldIgnoreClick(e.target, relevanceCell)) {
                console.log('Ignoring click on relevance cell');
                return;
            }
            activateRelevanceEdit(relevanceCell, e);
            return;
        }

        // Check for category cell
        const categoryCell = findCategoryCell(e.target);
        if (categoryCell) {
            if (shouldIgnoreClick(e.target, categoryCell)) {
                return;
            }
            activateCategoryEdit(categoryCell, e);
            return;
        }

        // Check for location cell
        const locationCell = findLocationCell(e.target);
        if (locationCell) {
            if (shouldIgnoreClick(e.target, locationCell)) {
                return;
            }
            activateLocationEdit(locationCell, e);
            return;
        }

        // Check for organization name cell
        const orgNameCell = findOrgNameCell(e.target);
        if (orgNameCell) {
            if (shouldIgnoreClick(e.target, orgNameCell)) {
                return;
            }
            activateOrgNameEdit(orgNameCell, e);
            return;
        }

        // Check for organization type cell
        const orgTypeCell = findOrgTypeCell(e.target);
        if (orgTypeCell) {
            if (shouldIgnoreClick(e.target, orgTypeCell)) {
                return;
            }
            activateOrgTypeEdit(orgTypeCell, e);
            return;
        }

        // Close any open edits if clicking outside
        closeAllEdits();
    }
    
    /**
     * Find category cell from click target
     */
    function findCategoryCell(target) {
        // Try closest first
        let cell = target.closest('.editable-category');
        if (cell) return cell;
        
        // Try finding via display span
        const displaySpan = target.closest('.category-display');
        if (displaySpan) {
            cell = displaySpan.closest('.editable-category');
            if (cell) return cell;
        }
        
        return null;
    }
    
    /**
     * Find location cell from click target
     */
    function findLocationCell(target) {
        // Try closest first
        let cell = target.closest('.editable-location');
        if (cell) return cell;
        
        // Try finding via display span
        const displaySpan = target.closest('.location-display');
        if (displaySpan) {
            cell = displaySpan.closest('.editable-location');
            if (cell) return cell;
        }
        
        return null;
    }
    
    /**
     * Find organization name cell from click target
     */
    function findOrgNameCell(target) {
        // Try closest first
        let cell = target.closest('.editable-organization-name');
        if (cell) return cell;
        
        // Try finding via display span
        const displaySpan = target.closest('.org-name-display');
        if (displaySpan) {
            cell = displaySpan.closest('.editable-organization-name');
            if (cell) return cell;
        }
        
        return null;
    }
    
    /**
     * Find organization type cell from click target
     */
    function findOrgTypeCell(target) {
        // Try closest first
        let cell = target.closest('.editable-organization-type');
        if (cell) return cell;
        
        // Try finding via display span
        const displaySpan = target.closest('.org-type-display');
        if (displaySpan) {
            cell = displaySpan.closest('.editable-organization-type');
            if (cell) return cell;
        }
        
        return null;
    }
    
    /**
     * Find relevance cell from click target
     */
    function findRelevanceCell(target) {
        // Try closest first
        let cell = target.closest('.editable-relevance');
        if (cell) return cell;
        
        // Try finding via display span
        const displaySpan = target.closest('.relevance-display');
        if (displaySpan) {
            cell = displaySpan.closest('.editable-relevance');
            if (cell) return cell;
        }
        
        return null;
    }
    
    /**
     * Check if click should be ignored (e.g., clicking on select/option/input)
     */
    function shouldIgnoreClick(target, cell) {
        // Don't activate if clicking on the edit input/select itself or its options
        if (target.closest('.category-edit') || target.closest('.location-edit') || target.closest('.org-type-edit') || target.closest('.org-name-edit') || target.closest('.relevance-edit')) {
            return true;
        }
        
        if (target.tagName === 'SELECT' || target.tagName === 'OPTION' || target.tagName === 'INPUT') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Activate category edit mode
     */
    function activateCategoryEdit(cell, event) {
        const edit = cell.querySelector('.category-edit');
        const display = cell.querySelector('.category-display');
        
        if (!edit || !display) return;
        
        // Only activate if edit is hidden
        if (edit.style.display !== 'none' && edit.style.display !== '') {
            return;
        }
        
        console.log('Activating category edit for record:', cell.getAttribute('data-record-id'));
        
        display.style.display = 'none';
        edit.style.display = 'block';
        edit.focus();
        edit.select(); // Select text for easy editing
        
        event.stopPropagation();
        event.preventDefault();
    }
    
    /**
     * Activate location edit mode
     */
    function activateLocationEdit(cell, event) {
        const edit = cell.querySelector('.location-edit');
        const display = cell.querySelector('.location-display');
        
        if (!edit || !display) return;
        
        // Only activate if edit is hidden
        if (edit.style.display !== 'none' && edit.style.display !== '') {
            return;
        }
        
        console.log('Activating location edit for record:', cell.getAttribute('data-record-id'));
        
        display.style.display = 'none';
        edit.style.display = 'block';
        edit.focus();
        edit.select(); // Select text for easy editing
        
        event.stopPropagation();
        event.preventDefault();
    }
    
    /**
     * Activate organization name edit mode
     */
    function activateOrgNameEdit(cell, event) {
        const edit = cell.querySelector('.org-name-edit');
        const display = cell.querySelector('.org-name-display');
        
        if (!edit || !display) return;
        
        // Only activate if edit is hidden
        if (edit.style.display !== 'none' && edit.style.display !== '') {
            return;
        }
        
        console.log('Activating organization name edit for record:', cell.getAttribute('data-record-id'));
        
        display.style.display = 'none';
        edit.style.display = 'block';
        edit.focus();
        edit.select(); // Select text for easy editing
        
        event.stopPropagation();
        event.preventDefault();
    }
    
    /**
     * Activate organization type edit mode
     */
    function activateOrgTypeEdit(cell, event) {
        const edit = cell.querySelector('.org-type-edit');
        const display = cell.querySelector('.org-type-display');
        
        if (!edit || !display) return;
        
        // Only activate if edit is hidden
        if (edit.style.display !== 'none' && edit.style.display !== '') {
            return;
        }
        
        console.log('Activating organization type edit for record:', cell.getAttribute('data-record-id'));
        
        display.style.display = 'none';
        edit.style.display = 'block';
        edit.focus();
        edit.select(); // Select text for easy editing
        
        event.stopPropagation();
        event.preventDefault();
    }
    
    /**
     * Activate relevance edit mode
     */
    function activateRelevanceEdit(cell, event) {
        console.log('activateRelevanceEdit called for cell:', cell);

        const edit = cell.querySelector('.relevance-edit');
        const display = cell.querySelector('.relevance-display');

        console.log('Found edit element:', edit);
        console.log('Found display element:', display);

        if (!edit || !display) {
            console.log('Missing edit or display element, returning');
            return;
        }

        // Only activate if edit is hidden
        console.log('Current edit display style:', edit.style.display);
        if (edit.style.display !== 'none' && edit.style.display !== '') {
            console.log('Edit already visible, returning');
            return;
        }

        console.log('Activating relevance edit for record:', cell.getAttribute('data-record-id'));

        display.style.display = 'none';
        edit.style.display = 'inline-block';
        edit.focus();
        edit.select();

        event.stopPropagation();
        event.preventDefault();
    }
    
    /**
     * Close all open edits
     */
    function closeAllEdits() {
        // Close category edits
        document.querySelectorAll('.category-edit').forEach(select => {
            if (select.style.display !== 'none') {
                const cell = select.closest('.editable-category');
                const display = cell ? cell.querySelector('.category-display') : null;
                if (display) {
                    display.style.display = '';
                    select.style.display = 'none';
                }
            }
        });
        
        // Close location edits
        document.querySelectorAll('.location-edit').forEach(input => {
            if (input.style.display !== 'none') {
                const cell = input.closest('.editable-location');
                const display = cell ? cell.querySelector('.location-display') : null;
                if (display) {
                    display.style.display = '';
                    input.style.display = 'none';
                }
            }
        });
        
        // Close organization name edits
        document.querySelectorAll('.org-name-edit').forEach(input => {
            if (input.style.display !== 'none') {
                const cell = input.closest('.editable-organization-name');
                const display = cell ? cell.querySelector('.org-name-display') : null;
                if (display) {
                    display.style.display = '';
                    input.style.display = 'none';
                }
            }
        });
        
        // Close organization type edits
        document.querySelectorAll('.org-type-edit').forEach(select => {
            if (select.style.display !== 'none') {
                const cell = select.closest('.editable-organization-type');
                const display = cell ? cell.querySelector('.org-type-display') : null;
                if (display) {
                    display.style.display = '';
                    select.style.display = 'none';
                }
            }
        });
        
        // Close relevance edits
        document.querySelectorAll('.relevance-edit').forEach(input => {
            if (input.style.display !== 'none') {
                const cell = input.closest('.editable-relevance');
                const display = cell ? cell.querySelector('.relevance-display') : null;
                if (display) {
                    display.style.display = '';
                    input.style.display = 'none';
                }
            }
        });
    }
    
    /**
     * Save location inline
     */
    async function saveLocationInline(recordId, newValue) {
        const saveKey = `location_${recordId}`;

        // Prevent double-save
        if (savingInProgress[saveKey]) {
            console.log('Save already in progress for location:', recordId);
            return;
        }

        const locationCell = document.querySelector(`.editable-location[data-record-id="${recordId}"]`);
        if (!locationCell) return;

        const display = locationCell.querySelector('.location-display');
        const edit = locationCell.querySelector('.location-edit');

        // Mark save as in progress
        savingInProgress[saveKey] = true;

        // Show loading
        display.textContent = 'שומר...';
        display.style.display = '';
        edit.style.display = 'none';
        
        try {
            // Use API module if available, otherwise fallback to direct fetch
            let saveData;
            if (window.API) {
                const currentRecord = await window.API.getRecord(recordId);
                if (!currentRecord || typeof currentRecord !== 'object') {
                    throw new Error('לא ניתן לטעון את הרשומה הנוכחית');
                }
                const updateData = {
                    ...currentRecord,
                    geographic_scope: newValue ? newValue.trim() : null
                };
                const updatedRecord = await window.API.saveRecord(updateData);
                saveData = { success: true, record: updatedRecord };
            } else {
                // Fallback to direct fetch
                const getResponse = await fetch(`api/records.php?id=${recordId}`);
                const getData = await getResponse.json();
                
                if (!getData.success) {
                    throw new Error(getData.error || 'שגיאה בטעינת הרשומה');
                }
                
                const updateData = {
                    ...getData.record,
                    geographic_scope: newValue ? newValue.trim() : null
                };
                
                const saveResponse = await fetch('api/records.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });
                
                saveData = await saveResponse.json();
            }
            
            if (!saveData.success) {
                throw new Error(saveData.error || 'שגיאה בשמירה');
            }
            
            // Update display
            const displayValue = newValue ? newValue.trim() : '-';
            display.textContent = displayValue;
            locationCell.setAttribute('data-current-value', newValue ? newValue.trim() : '');
            
            // Update the input value
            if (edit.tagName === 'INPUT') {
                edit.value = newValue ? newValue.trim() : '';
            }
            
        } catch (error) {
            const errorMessage = error?.message || (error ? String(error) : 'שגיאה לא ידועה');
            console.error('Error saving location:', errorMessage, error);
            const currentValue = locationCell.getAttribute('data-current-value') || '-';
            display.textContent = currentValue === '' ? '-' : currentValue;
            alert('שגיאה בשמירת המיקום: ' + errorMessage);
        } finally {
            // Clear saving flag
            delete savingInProgress[saveKey];
        }
    }

    /**
     * Save category inline
     */
    async function saveCategoryInline(recordId, newValue) {
        const saveKey = `category_${recordId}`;

        // Prevent double-save
        if (savingInProgress[saveKey]) {
            console.log('Save already in progress for category:', recordId);
            return;
        }

        const categoryCell = document.querySelector(`.editable-category[data-record-id="${recordId}"]`);
        if (!categoryCell) return;

        const display = categoryCell.querySelector('.category-display');
        const edit = categoryCell.querySelector('.category-edit');

        // Mark save as in progress
        savingInProgress[saveKey] = true;

        // Show loading
        display.textContent = 'שומר...';
        display.style.display = '';
        edit.style.display = 'none';
        
        try {
            // Use API module if available, otherwise fallback to direct fetch
            let saveData;
            if (window.API) {
                const currentRecord = await window.API.getRecord(recordId);
                if (!currentRecord || typeof currentRecord !== 'object') {
                    throw new Error('לא ניתן לטעון את הרשומה הנוכחית');
                }
                const updateData = {
                    ...currentRecord,
                    topic_category: newValue || null
                };
                const updatedRecord = await window.API.saveRecord(updateData);
                saveData = { success: true, record: updatedRecord };
            } else {
                // Fallback to direct fetch
                const getResponse = await fetch(`api/records.php?id=${recordId}`);
                const getData = await getResponse.json();
                
                if (!getData.success) {
                    throw new Error(getData.error || 'שגיאה בטעינת הרשומה');
                }
                
                const updateData = {
                    ...getData.record,
                    topic_category: newValue || null
                };
                
                const saveResponse = await fetch('api/records.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });
                
                saveData = await saveResponse.json();
            }
            
            if (!saveData.success) {
                throw new Error(saveData.error || 'שגיאה בשמירה');
            }
            
            // Update display - show label if exists, otherwise show the value itself
            const newLabel = newValue ? (window.getLabel ? window.getLabel(newValue, 'topicCategory') : null) || newValue : '-';
            display.textContent = newLabel;
            categoryCell.setAttribute('data-current-value', newValue || '');
            
            // Update the input value
            if (edit.tagName === 'INPUT') {
                edit.value = newValue || '';
            }
            
        } catch (error) {
            const errorMessage = error?.message || (error ? String(error) : 'שגיאה לא ידועה');
            console.error('Error saving category:', errorMessage, error);
            display.textContent = window.getLabel ? window.getLabel(categoryCell.getAttribute('data-current-value'), 'topicCategory') : categoryCell.getAttribute('data-current-value') || '-';
            alert('שגיאה בשמירת הקטגוריה: ' + errorMessage);
        } finally {
            // Clear saving flag
            delete savingInProgress[saveKey];
        }
    }

    /**
     * Save relevance inline
     */
    async function saveRelevanceInline(recordId, newValue) {
        console.log('=== SAVE RELEVANCE START ===', { recordId, newValue });

        const saveKey = `relevance_${recordId}`;

        // Prevent double-save
        if (savingInProgress[saveKey]) {
            console.log('Save already in progress for relevance:', recordId);
            return;
        }

        const relevanceCell = document.querySelector(`.editable-relevance[data-record-id="${recordId}"]`);
        if (!relevanceCell) {
            console.error('Relevance cell not found for record:', recordId);
            return;
        }

        const display = relevanceCell.querySelector('.relevance-display');
        const edit = relevanceCell.querySelector('.relevance-edit');

        // Mark save as in progress
        savingInProgress[saveKey] = true;

        // Show loading
        display.textContent = 'שומר...';
        display.style.display = '';
        edit.style.display = 'none';

        try {
            // Step 1: Fetch current record directly (bypass API module for debugging)
            console.log('Step 1: Fetching record', recordId);
            const getResponse = await fetch(`api/records.php?id=${recordId}`);
            console.log('Step 1: GET response status:', getResponse.status);

            const getResponseText = await getResponse.text();
            console.log('Step 1: GET response text:', getResponseText.substring(0, 500));

            let getData;
            try {
                getData = JSON.parse(getResponseText);
            } catch (parseError) {
                throw new Error('שגיאה בפענוח תגובת GET: ' + parseError.message + ' | Response: ' + getResponseText.substring(0, 200));
            }

            if (!getData.success) {
                throw new Error('שגיאה בטעינת רשומה: ' + (getData.error || 'Unknown error'));
            }

            console.log('Step 1: Record loaded successfully:', getData.record?.id);

            // Step 2: Create update data
            const updateData = {
                ...getData.record,
                relevance_level: newValue ? parseInt(newValue) : null
            };
            console.log('Step 2: Update data created, relevance_level:', updateData.relevance_level);

            // Step 3: Save record
            console.log('Step 3: Saving record...');
            const saveResponse = await fetch('api/records.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(updateData)
            });
            console.log('Step 3: POST response status:', saveResponse.status);

            const saveResponseText = await saveResponse.text();
            console.log('Step 3: POST response text:', saveResponseText.substring(0, 500));

            let saveData;
            try {
                saveData = JSON.parse(saveResponseText);
            } catch (parseError) {
                throw new Error('שגיאה בפענוח תגובת POST: ' + parseError.message + ' | Response: ' + saveResponseText.substring(0, 200));
            }

            if (!saveData.success) {
                throw new Error('שגיאה בשמירה: ' + (saveData.error || 'Unknown error'));
            }

            console.log('Step 3: Record saved successfully');

            // Update display
            const relevanceValue = newValue ? parseInt(newValue) : null;
            if (relevanceValue && relevanceValue >= 1 && relevanceValue <= 5) {
                const color = window.getRelevanceColor ? window.getRelevanceColor(relevanceValue) : '#666';
                display.innerHTML = `<span style="font-weight: bold; color: ${color}; font-size: 16px;">${relevanceValue}</span>`;
            } else {
                display.textContent = '-';
            }
            relevanceCell.setAttribute('data-current-value', newValue || '');

            // Update the input value
            if (edit.tagName === 'INPUT') {
                edit.value = newValue || '';
            }

            console.log('=== SAVE RELEVANCE SUCCESS ===');

        } catch (error) {
            const errorMessage = (error && error.message) ? error.message : (error ? String(error) : 'שגיאה לא ידועה');
            console.error('=== SAVE RELEVANCE FAILED ===');
            console.error('Error type:', typeof error);
            console.error('Error:', error);
            console.error('Error message:', errorMessage);

            const currentValue = relevanceCell.getAttribute('data-current-value');
            if (currentValue) {
                const color = window.getRelevanceColor ? window.getRelevanceColor(parseInt(currentValue)) : '#666';
                display.innerHTML = `<span style="font-weight: bold; color: ${color};">${currentValue}</span>`;
            } else {
                display.textContent = '-';
            }
            alert('שגיאה בשמירת הרלוונטיות: ' + errorMessage);
        } finally {
            // Clear saving flag
            delete savingInProgress[saveKey];
        }
    }

    /**
     * Save organization name inline
     */
    async function saveOrganizationNameInline(recordId, newValue) {
        const saveKey = `orgName_${recordId}`;

        // Prevent double-save
        if (savingInProgress[saveKey]) {
            console.log('Save already in progress for organization name:', recordId);
            return;
        }

        const orgNameCell = document.querySelector(`.editable-organization-name[data-record-id="${recordId}"]`);
        if (!orgNameCell) return;

        const display = orgNameCell.querySelector('.org-name-display');
        const edit = orgNameCell.querySelector('.org-name-edit');

        // Mark save as in progress
        savingInProgress[saveKey] = true;

        // Show loading
        display.textContent = 'שומר...';
        display.style.display = '';
        edit.style.display = 'none';

        try {
            // Use API module if available, otherwise fallback to direct fetch
            let saveData;
            if (window.API) {
                const currentRecord = await window.API.getRecord(recordId);
                if (!currentRecord || typeof currentRecord !== 'object') {
                    throw new Error('לא ניתן לטעון את הרשומה הנוכחית');
                }
                const updateData = {
                    ...currentRecord,
                    organization_name: newValue ? newValue.trim() : null
                };
                const updatedRecord = await window.API.saveRecord(updateData);
                saveData = { success: true, record: updatedRecord };
            } else {
                // Fallback to direct fetch
                const getResponse = await fetch(`api/records.php?id=${recordId}`);
                const getData = await getResponse.json();

                if (!getData.success) {
                    throw new Error(getData.error || 'שגיאה בטעינת הרשומה');
                }

                const updateData = {
                    ...getData.record,
                    organization_name: newValue ? newValue.trim() : null
                };
                
                const saveResponse = await fetch('api/records.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });
                
                saveData = await saveResponse.json();
            }
            
            if (!saveData.success) {
                throw new Error(saveData.error || 'שגיאה בשמירה');
            }
            
            // Update display
            const displayValue = newValue ? newValue.trim() : '-';
            display.textContent = displayValue;
            orgNameCell.setAttribute('data-current-value', newValue ? newValue.trim() : '');
            
            // Update the input value
            if (edit.tagName === 'INPUT') {
                edit.value = newValue ? newValue.trim() : '';
            }
            
        } catch (error) {
            const errorMessage = error?.message || (error ? String(error) : 'שגיאה לא ידועה');
            console.error('Error saving organization name:', errorMessage, error);
            const currentValue = orgNameCell.getAttribute('data-current-value') || '-';
            display.textContent = currentValue === '' ? '-' : currentValue;
            alert('שגיאה בשמירת שם הארגון: ' + errorMessage);
        } finally {
            // Clear saving flag
            delete savingInProgress[saveKey];
        }
    }

    /**
     * Save organization type inline
     */
    async function saveOrganizationTypeInline(recordId, newValue) {
        const saveKey = `orgType_${recordId}`;

        // Prevent double-save
        if (savingInProgress[saveKey]) {
            console.log('Save already in progress for organization type:', recordId);
            return;
        }

        const orgTypeCell = document.querySelector(`.editable-organization-type[data-record-id="${recordId}"]`);
        if (!orgTypeCell) return;

        const display = orgTypeCell.querySelector('.org-type-display');
        const edit = orgTypeCell.querySelector('.org-type-edit');

        // Mark save as in progress
        savingInProgress[saveKey] = true;

        // Show loading
        display.textContent = 'שומר...';
        display.style.display = '';
        edit.style.display = 'none';

        try {
            // Use API module if available, otherwise fallback to direct fetch
            let saveData;
            if (window.API) {
                const currentRecord = await window.API.getRecord(recordId);
                if (!currentRecord || typeof currentRecord !== 'object') {
                    throw new Error('לא ניתן לטעון את הרשומה הנוכחית');
                }
                const updateData = {
                    ...currentRecord,
                    organization_type: newValue || null
                };
                const updatedRecord = await window.API.saveRecord(updateData);
                saveData = { success: true, record: updatedRecord };
            } else {
                // Fallback to direct fetch
                const getResponse = await fetch(`api/records.php?id=${recordId}`);
                const getData = await getResponse.json();

                if (!getData.success) {
                    throw new Error(getData.error || 'שגיאה בטעינת הרשומה');
                }

                const updateData = {
                    ...getData.record,
                    organization_type: newValue || null
                };
                
                const saveResponse = await fetch('api/records.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });
                
                saveData = await saveResponse.json();
            }
            
            if (!saveData.success) {
                throw new Error(saveData.error || 'שגיאה בשמירה');
            }
            
            // Update display - show label if exists, otherwise show the value itself
            const newLabel = newValue ? (window.getLabel ? window.getLabel(newValue, 'organizationType') : null) || newValue : '-';
            display.textContent = newLabel;
            orgTypeCell.setAttribute('data-current-value', newValue || '');
            
            // Update the input value
            if (edit.tagName === 'INPUT') {
                edit.value = newValue || '';
            }
            
        } catch (error) {
            const errorMessage = error?.message || (error ? String(error) : 'שגיאה לא ידועה');
            console.error('Error saving organization type:', errorMessage, error);
            display.textContent = window.getLabel ? window.getLabel(orgTypeCell.getAttribute('data-current-value'), 'organizationType') : orgTypeCell.getAttribute('data-current-value') || '-';
            alert('שגיאה בשמירת סוג הארגון: ' + errorMessage);
        } finally {
            // Clear saving flag
            delete savingInProgress[saveKey];
        }
    }

    // Export functions to global scope
    window.InlineEdit = {
        init: init,
        saveCategoryInline: saveCategoryInline,
        saveLocationInline: saveLocationInline,
        saveOrganizationNameInline: saveOrganizationNameInline,
        saveOrganizationTypeInline: saveOrganizationTypeInline,
        saveRelevanceInline: saveRelevanceInline
    };
    
    // Also export as global functions for backward compatibility with HTML onclick
    window.saveCategoryInline = saveCategoryInline;
    window.saveLocationInline = saveLocationInline;
    window.saveOrganizationNameInline = saveOrganizationNameInline;
    window.saveOrganizationTypeInline = saveOrganizationTypeInline;
    window.saveRelevanceInline = saveRelevanceInline;
    
    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();


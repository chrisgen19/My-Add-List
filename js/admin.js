document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('sortable-list');
    const addButton = document.getElementById('add-item');
    const newItemInput = document.getElementById('new-item-text');
    
    let draggedItem = null;
    let draggedItemIndex = null;
    
    addButton.addEventListener('click', function() {
        const text = newItemInput.value.trim();
        if (text) {
            addNewItem(text);
            newItemInput.value = '';
            saveItems();
        }
    });
    
    newItemInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            addButton.click();
        }
    });
    
    list.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('li').remove();
            saveItems();
        }
    });
    
    list.addEventListener('dragstart', function(e) {
        if (e.target.tagName === 'LI') {
            draggedItem = e.target;
            draggedItemIndex = Array.from(list.children).indexOf(draggedItem);
            e.target.classList.add('dragging');
            
            e.dataTransfer.setData('text/plain', '');
            e.dataTransfer.effectAllowed = 'move';
        }
    });
    
    list.addEventListener('dragend', function(e) {
        if (e.target.tagName === 'LI') {
            e.target.classList.remove('dragging');
            draggedItem = null;
            draggedItemIndex = null;
            saveItems();
        }
    });
    
    list.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (!draggedItem) return;
        
        const currentTarget = e.target.closest('li');
        if (!currentTarget || currentTarget === draggedItem) return;
        
        const currentRect = currentTarget.getBoundingClientRect();
        const currentMiddle = currentRect.top + currentRect.height / 2;
        
        if (e.clientY < currentMiddle) {
            currentTarget.parentNode.insertBefore(draggedItem, currentTarget);
        } else {
            currentTarget.parentNode.insertBefore(draggedItem, currentTarget.nextSibling);
        }
    });
    
    function initializeDraggable() {
        Array.from(list.children).forEach(item => {
            item.setAttribute('draggable', 'true');
        });
    }
    
    function addNewItem(text) {
        const id = 'item_' + Date.now();
        const li = document.createElement('li');
        li.className = 'list-item';
        li.setAttribute('draggable', 'true');
        li.setAttribute('data-id', id);
        li.innerHTML = `
            <span class="item-text">${escapeHtml(text)}</span>
            <span class="item-handle">☰</span>
            <button class="remove-item">×</button>
        `;
        list.appendChild(li);
    }
    
    function saveItems() {
        const items = Array.from(list.children).map(li => ({
            id: li.getAttribute('data-id'),
            text: li.querySelector('.item-text').textContent
        }));
        
        jQuery.post(myAddListAjax.ajaxurl, {
            action: 'save_list_items',
            nonce: myAddListAjax.nonce,
            items: items
        })
        .done(function(response) {
            if (!response.success) {
                console.error('Error saving items:', response.data);
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Ajax error:', error);
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    initializeDraggable();
});
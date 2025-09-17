jQuery(document).ready(function($){
    let rowIndex = $('#liquid-hover-table tbody tr').length;

    // Add new row
    $('#add-row').on('click', function(){
        let index = rowIndex++;
        $('#liquid-hover-table tbody').append(`
            <tr>
                <td>
                    <div class="media-preview"></div>
                    <input type="hidden" name="liquid_hover_items[${index}][image1]" />
                    <button class="button select-media">Select Image</button>
                </td>
                <td>
                    <div class="media-preview"></div>
                    <input type="hidden" name="liquid_hover_items[${index}][image2]" />
                    <button class="button select-media">Select Image</button>
                </td>
                <td>
                    <select name="liquid_hover_items[${index}][aspect_ratio]">
                        <option value="16/9">16/9</option>
                        <option value="4/3">4/3</option>
                        <option value="1/1" selected>1/1</option>
                        <option value="21/9">21/9</option>
                    </select>
                </td>
                <td><code>[liquid_hover id="${index}"]</code></td>
                <td><button type="button" class="button remove-row">X</button></td>
            </tr>
        `);
    });

    // Remove row
    $(document).on('click', '.remove-row', function(){
        $(this).closest('tr').remove();
    });

    // Media uploader
    $(document).on('click', '.select-media', function(e){
        e.preventDefault();
        let button = $(this);
        let preview = button.closest('td').find('.media-preview');
        let input = button.closest('td').find('input');

        let frame = wp.media({
            title: 'Select or Upload Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        frame.on('select', function(){
            let attachment = frame.state().get('selection').first().toJSON();

            // ✅ Fallback to full image URL if no thumbnail exists
            let imgURL = (attachment.sizes && attachment.sizes.thumbnail) 
                ? attachment.sizes.thumbnail.url 
                : attachment.url;

            preview.html('<img src="'+imgURL+'" class="thumb" />');
            input.val(attachment.id); // ✅ store ID, not URL
        });

        frame.open();
    });
});

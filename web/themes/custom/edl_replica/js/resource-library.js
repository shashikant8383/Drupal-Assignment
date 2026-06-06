(function (Drupal, once) {
  Drupal.behaviors.edlResourceLibrary = {
    attach(context) {
      once('edl-resource-filter-enhance', '.edl-resource-filters select[name="category"], .edl-resource-filters select[name="resource_type"]', context)
        .forEach((select) => {
          const wrapper = document.createElement('div');
          wrapper.className = 'edl-checkbox-list';

          Array.from(select.options).forEach((option) => {
            if (option.value === 'All') {
              return;
            }

            const item = document.createElement('label');
            item.className = 'edl-checkbox-list__item';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = option.value;
            checkbox.checked = option.selected;

            const labelText = document.createElement('span');
            labelText.textContent = option.textContent;

            item.append(checkbox, labelText);
            wrapper.append(item);

            checkbox.addEventListener('change', () => {
              wrapper.querySelectorAll('input[type="checkbox"]').forEach((input) => {
                if (input !== checkbox) {
                  input.checked = false;
                }
              });

              Array.from(select.options).forEach((selectOption) => {
                selectOption.selected = selectOption.value === checkbox.value && checkbox.checked;
              });

              select.form.requestSubmit();
            });
          });

          select.classList.add('edl-resource-original-select');
          select.insertAdjacentElement('afterend', wrapper);
        });

      once('edl-resource-sort-submit', '.edl-resource-filters select[name="sort_by"]', context)
        .forEach((select) => {
          select.addEventListener('change', () => select.form.requestSubmit());
        });
    },
  };
})(Drupal, once);

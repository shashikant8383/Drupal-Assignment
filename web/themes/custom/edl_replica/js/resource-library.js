(function () {
  function enhanceResourceFilters(context) {
    const root = context || document;
    const heading = document.querySelector('.edl-resource-filters__heading h2');

    root
      .querySelectorAll('.edl-resource-filters select[name="category"], .edl-resource-filters select[name="resource_type"], .edl-resource-filters select[name="field_resource_category_target_id"], .edl-resource-filters select[name="field_resource_type_target_id"]')
      .forEach((select) => {
        if (select.dataset.edlEnhanced === 'true') {
          return;
        }

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
          checkbox.dataset.filterSelectName = select.name;

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

        select.dataset.edlEnhanced = 'true';
        select.classList.add('edl-resource-original-select');
        select.insertAdjacentElement('afterend', wrapper);
      });

    const updateFilterCount = () => {
      if (!heading) {
        return;
      }

      const selectedFilters = document.querySelectorAll('.edl-checkbox-list__item input:checked').length;
      heading.textContent = `Filters (${selectedFilters})`;
    };

    document.querySelectorAll('.edl-checkbox-list__item input').forEach((checkbox) => {
      checkbox.addEventListener('change', updateFilterCount);
    });
    updateFilterCount();

    root.querySelectorAll('.edl-resource-filters select[name="sort_by"]').forEach((select) => {
      if (select.dataset.edlSortEnhanced === 'true') {
        return;
      }

      select.dataset.edlSortEnhanced = 'true';
      select.addEventListener('change', () => select.form.requestSubmit());
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => enhanceResourceFilters(document));
  }
  else {
    enhanceResourceFilters(document);
  }
})();

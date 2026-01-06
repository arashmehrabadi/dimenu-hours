(function ($) {
  // Weekly intervals
  function addIntervalRow(day) {
    const container = $('.intervals[data-day="' + day + '"]');
    if (!container.length) return;

    const nextIndex = container.find('.interval-row').length;
    const row = $('<div class="interval-row">\
      <input type="time" name="weekly[' + day + '][intervals][' + nextIndex + '][start]" value="" />\
      <span class="sep">–</span>\
      <input type="time" name="weekly[' + day + '][intervals][' + nextIndex + '][end]" value="" />\
      <button type="button" class="button remove-interval" aria-label="Remove">×</button>\
    </div>');
    container.append(row);
  }

  function bindEvents() {
    $('.dimenu-hours-weekly').on('click', '.add-interval', function () {
      const day = $(this).data('day');
      addIntervalRow(day);
    });

    $('.dimenu-hours-weekly').on('click', '.remove-interval', function () {
      const container = $(this).closest('.intervals');
      $(this).closest('.interval-row').remove();
      if (container.find('.interval-row').length === 0) {
        const day = container.data('day');
        addIntervalRow(day);
      }
    });

    $('.dimenu-hours-weekly').on('change', 'input[type="checkbox"][name*="[is_closed]"]', function () {
      const row = $(this).closest('tr');
      const disabled = $(this).is(':checked');
      row.find('.intervals input, .intervals button, .add-interval').prop('disabled', disabled);
      row.toggleClass('is-closed', disabled);
    });

    // apply initial disabled state
    $('.dimenu-hours-weekly input[type="checkbox"][name*="[is_closed]"]').trigger('change');
  }

  // Exceptions
  function addExceptionRow() {
    const body = $('#dimenu-exceptions-body');
    const next = body.find('.exception-row').length;
    const row = $('<tr class="exception-row">\
      <td>\
        <select name="exceptions[' + next + '][type]" class="exception-type">\
          <option value="closed">Closed</option>\
          <option value="special_hours">Special hours</option>\
        </select>\
      </td>\
      <td><input type="date" name="exceptions[' + next + '][start_date]" value="" /></td>\
      <td><input type="date" name="exceptions[' + next + '][end_date]" value="" /></td>\
      <td>\
        <div class="ex-intervals" data-index="' + next + '">\
          <div class="interval-row">\
            <input type="time" name="exceptions[' + next + '][intervals][0][start]" value="" />\
            <span class="sep">–</span>\
            <input type="time" name="exceptions[' + next + '][intervals][0][end]" value="" />\
            <button type="button" class="button remove-ex-interval" aria-label="Remove interval">×</button>\
          </div>\
        </div>\
        <button type="button" class="button add-ex-interval" data-ex="' + next + '">Add interval</button>\
      </td>\
      <td><button type="button" class="button remove-exception" aria-label="Remove exception">×</button></td>\
    </tr>');
    body.append(row);
  }

  function addExInterval(exIndex) {
    const container = $('.ex-intervals[data-index="' + exIndex + '"]');
    if (!container.length) return;
    const next = container.find('.interval-row').length;
    const row = $('<div class="interval-row">\
      <input type="time" name="exceptions[' + exIndex + '][intervals][' + next + '][start]" value="" />\
      <span class="sep">–</span>\
      <input type="time" name="exceptions[' + exIndex + '][intervals][' + next + '][end]" value="" />\
      <button type="button" class="button remove-ex-interval" aria-label="Remove interval">×</button>\
    </div>');
    container.append(row);
  }

  function bindExceptions() {
    $('#add-exception-row').on('click', function () {
      addExceptionRow();
    });

    $('.dimenu-hours-exceptions').on('click', '.remove-exception', function () {
      $(this).closest('.exception-row').remove();
    });

    $('.dimenu-hours-exceptions').on('click', '.add-ex-interval', function () {
      const idx = $(this).data('ex');
      addExInterval(idx);
    });

    $('.dimenu-hours-exceptions').on('click', '.remove-ex-interval', function () {
      const container = $(this).closest('.ex-intervals');
      $(this).closest('.interval-row').remove();
      if (container.find('.interval-row').length === 0) {
        const idx = container.data('index');
        addExInterval(idx);
      }
    });

    $('.dimenu-hours-exceptions').on('change', '.exception-type', function () {
      const row = $(this).closest('.exception-row');
      const isSpecial = $(this).val() === 'special_hours';
      row.toggleClass('is-special', isSpecial);
      const inputs = row.find('.ex-intervals input, .add-ex-interval, .remove-ex-interval');
      inputs.prop('disabled', !isSpecial);
    });

    $('.dimenu-hours-exceptions .exception-type').trigger('change');
  }

  $(document).ready(function () {
    bindEvents();
    bindExceptions();
  });
})(jQuery);

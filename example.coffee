mobileCalendarHoverListener = ->
  if $('html').hasClass('touchevents') || $(window).width() <= 768
    container = $('#mri-calendar-mobile-events-container')
    $('td.has-events').click ->
      target    = $(this)
      container
        .fadeOut 'fast', ->
          container
            .empty()
            .append(target.find('.mri-calendar-events').html())
            .prepend('<a href="#" id="close-mri-calendar-mobile-events-container"><i class="fa fa-times"></i></a>')
          container.fadeIn 'fast'
    $(document).on 'click', '#close-mri-calendar-mobile-events-container', (event) ->
      event.preventDefault()
      container.fadeOut 'fast', ->
        container.empty()

$(document).ready ->
  mobileCalendarHoverListener()

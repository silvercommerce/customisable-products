import ImageZoom from 'js-image-zoom';

(function (document, window) {
  /**
   * Better cross browser support for XHR, thanks to this post:
   * https://stackoverflow.com/questions/3470895/small-ajax-javascript-library
   * for the idea
   */
  function createXhr() {
    let xhr;
    if (window.ActiveXObject) {
      try {
        // eslint-disable-next-line no-undef
        xhr = new ActiveXObject('Microsoft.XMLHTTP');
      } catch (e) {
        alert(e.message);
        xhr = null;
      }
    } else {
      xhr = new XMLHttpRequest();
    }

    return xhr;
  }

  function xhrRequest(event, fields) {
    const form = document
      .querySelector('form.product-customisation-form');
    const link = form.dataset.customisationLink;
    const xhr = createXhr();
    let params = '?o=';

    // Find customisation fields and append their
    // values to the URL
    fields.forEach(element => {
      let value = 0;

      // If no value, set to 0
      if (element.value > 0) {
        value = element.value;
      }

      params += value;

      // If not last element, add a comma
      if (element !== fields[fields.length - 1]) {
        params += ',';
      }
    });

    form.classList.add('loading');

    // Handle json response
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4 && xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);

        const priceField = document
          .querySelector('.catalogue-product-summary .catalogue-product-price');
        const mainImage = document
          .querySelector('.commerce-product .product-main-image');

        if (document.body.contains(priceField)) {
          priceField.innerHTML = response.price;
        }

        if (document.body.contains(mainImage)) {
          const image = mainImage.querySelector('img');

          mainImage.style.opacity = 0.5;
          image.src = response.isrc;
          const options = {
            width: mainImage.clientWidth,
            height: mainImage.clientHeight,
            offset: { vertical: 0, horizontal: 10 },
            zoomPosition: 'original',
          };

          image.onload = () => {
            options.img = response.isrc;
            window.imageZoom.kill();
            window.imageZoom = new ImageZoom(mainImage, options);
            mainImage.style.opacity = 1;
          };
        }
      }
    };

    // Remove loading state after request
    xhr.onloadend = () => {
      form.classList.remove('loading');
    };

    xhr.open('GET', link + params, true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send();
  }

  const customfields = document
    .querySelectorAll('form.product-customisation-form select');

  customfields.forEach((element) => {
    if (!element.classList.contains('product-customisation-field')) {
      return;
    }

    element.addEventListener(
      'change',
      (event) => { xhrRequest(event, customfields); }
    );
  });
}(document, window));

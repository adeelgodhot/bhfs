define([], function () {
    'use strict';

    /**
     *
     * @param {string} shapeContent
     * @param {string} newColor
     * @param {boolean} isTransparent
     * @return {string}
     */
    return function (shapeContent, newColor, isTransparent) {
        var parser = new DOMParser(),
            svgDom = parser.parseFromString(shapeContent, 'image/svg+xml'),
            result = null,
            serializer = new XMLSerializer(),
            colorElements = [],
            vectorColor,
            colorElement;

        if (svgDom.getElementsByTagName('svg').length) {
            if (isTransparent) {
                colorElements = svgDom.getElementsByTagName('g');

                if (colorElements.length === 0) {
                    colorElements = svgDom.getElementsByTagName('path');
                }

                if (colorElements[0] instanceof Element) {
                    colorElements[0].setAttribute('stroke', newColor);
                }
            } else {
                colorElements = svgDom.getElementsByTagName('path');

                for (colorElement of colorElements) {
                    vectorColor = colorElement.getAttribute('fill');

                    if (vectorColor && vectorColor.toUpperCase() !== '#FFFFFF') {
                        colorElement.setAttribute('fill', newColor);

                        break;
                    }
                }
            }

            result = serializer.serializeToString(svgDom);
        }

        return result;
    }
});

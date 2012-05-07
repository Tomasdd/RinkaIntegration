var locationService = {
    countryNodeId: null,
    folderId: null,

    options: {
        fieldId: null,
        fieldName: null,
        data: null,
        translations: null
    },

    locationServiceStatus: false,

    isLocationServiceReady: function() {
        return locationService.locationServiceStatus;
    },

    init: function(folderId, fieldName) {
        locationService.folderId = folderId;

        this.remoteService.getOptions(function(data) {
            locationService.options = data;
            locationService.options.fieldName = fieldName;
            locationService.locationServiceStatus = true;
            jQuery('#loc_country').change();
        });
    },

    selectOption: function(nodeId, nodeType, countryNodeId) {
        if (locationService.writeDataToInput(nodeType, nodeId)) {
            return;
        }

        locationService.countryNodeId = countryNodeId;
        jQuery('#loc_'+nodeType+' option[value='+nodeId+']').attr('selected', 'selected');
        jQuery('#loc_'+nodeType).change();
    },

    addInputText: function(node) {
        var selectNode = jQuery('#loc_'+node);

        if (selectNode.parent().find('.city-list-custom').length > 0) {
            return;
        }

        var inputHtml = '<input class="city-list-custom" type="text" value="" name="'+selectNode.attr('name')+'" />';
        inputHtml     = inputHtml + '<input type="hidden" name="'+locationService.options.fieldName+'[custom][]" value="'+node+'" />';
        selectNode.attr('name', locationService.options.fieldName + '[tmp]['+ node +']');
        jQuery('#div_loc_'+node).append(inputHtml);
    },

    inputTextExists: function(nodeType) {
        return Boolean(jQuery('input[name="'+locationService.options.fieldName+'[data_'+nodeType+']"]').length);
    },

    writeDataToInput: function(nodeType, value) {
        if (locationService.inputTextExists(nodeType)) {
            jQuery('input[name="'+locationService.options.fieldName+'[data_'+nodeType+']"]').val(value);
            return true;
        }

        return false;
    },

    removeInputTexts: function(node) {
        jQuery('#div_loc_'+node).find('input').each(function() {
            jQuery(this).remove();
        });
        jQuery('#div_loc_'+node).find('select').attr('name', locationService.options.fieldName + '[data_'+node+']');
    },

    getChildren: function (nodeId, nodeType) {
        if (nodeId == nodeType) {
            return false;
        }
        if (!locationService.isLocationServiceReady()) {
            return false;
        }

        if (isNaN(nodeId)) {
            this.addInputText(nodeId);
        } else {
            this.removeInputTexts(nodeType);
        }

        var countryNodeId = (locationService.countryNodeId === null) ? this.getCountryNodeId() : locationService.countryNodeId;
        if (nodeType == 'country') {
            countryNodeId = nodeId;
        }

        if (nodeId === '') {
            var prevSibling = jQuery('#div_loc_'+nodeType).prev();

            if (prevSibling.length > 0) {
                var prevSiblingNodeType = prevSibling.find('select').attr('id').substr(prevSibling.find('select').attr('id').indexOf('_')+1);
                this.selectOption(prevSibling.find('select option:selected').val(), prevSiblingNodeType, countryNodeId);
            } else {
                this.deleteNextSiblings('country');
            }
            return null;
        }

        var self = this;
        this.remoteService.getChildren(nodeId, countryNodeId, function(data) {
            self.updateList({
                from: nodeType,
                data: data
            });
        });
    },

    deleteNextSiblings : function(from) {
        jQuery('#div_loc_'+from).nextAll().each(function() {
            jQuery(this).remove();
        });
    },

    getCountryNodeId: function() {
        return jQuery('#loc_country option:selected').val();
    },

    updateList: function (o) {
        if (o === null) {
            return;
        }
        this.deleteNextSiblings(o.from);

        var preparedList = '';

        var makeList = function() {
            jQuery.each(o.data, function(administrativeDivision, child) {
                for (i in locationService.options.data[administrativeDivision]) {
                    if (locationService.isAbove(locationService.options.data[administrativeDivision][i].parent_type, o.from)) {
                        child.push(locationService.options.data[administrativeDivision][i]);
                    }
                }
                locationService.options.data[administrativeDivision] = child;

                preparedList = preparedList + '<div id="div_loc_'+administrativeDivision+'">';
                preparedList = preparedList + (locationService.options.translations[administrativeDivision] !== undefined ? locationService.options.translations[administrativeDivision] : administrativeDivision) + '<br/>';
                if (child.length === 0) {
                    preparedList = preparedList + '<input type="hidden" name="'+locationService.options.fieldName+'[custom][]" value="'+administrativeDivision+'" />';
                    preparedList = preparedList + '<input type="text" name="'+locationService.options.fieldName+'[data_'+administrativeDivision+']" value="" />';
                } else {
                    preparedList = preparedList + '<select class="city-list-item" id="loc_'+administrativeDivision+'" name="'+locationService.options.fieldName+'[data_'+administrativeDivision+']" onchange="locationService.getChildren(this.options[this.selectedIndex].value, \''+administrativeDivision+'\')">';
                    preparedList = preparedList + '<option value=""></option>';
                    preparedList = preparedList + '<option value="'+administrativeDivision+'">---- Kita ----</option>';
                    jQuery.each(child, function(i, childInfo) {
                        preparedList = preparedList + '<option value="'+childInfo.id+'">'+childInfo.value+'</option>';
                    });
                    preparedList = preparedList + '</select></div>';
                }
            });
        }();

        jQuery('#div_loc_'+o.from).parent().append(preparedList);
    },

    isAbove: function(d1, d2) {
        for (var i in locationService.options.data) {
            if (i == d2) {
                return false;
            } else if (i == d1) {
                return true;
            }
        }
    },

    remoteService: {
        baseUrl: 'http://rinka.lt',
        data: {},                       // holds information from remote server and pending callbacks
        get: function(keys, data) {
            if (data === undefined) {
                data = this.data;
            }
            key = keys.shift();
            if (data[key] === undefined) {
                return false;
            } else if (keys.length === 0) {
                return data[key];
            } else {
                return this.get(keys, data[key]);
            }
        },
        set: function(keys, value, data) {
            if (data === undefined) {
                this.data = this.set(keys, value, this.data);
            } else {
                key = keys.shift();
                if (keys.length === 0) {
                    data[key] = value;
                } else {
                    if (data[key] === undefined) {
                        data[key] = {};
                    }
                    data[key] = this.set(keys, value, data[key]);
                }
                return data;
            }
        },
        childrenLoaded: function(data) {    // called when data is returned from server
            this.set(['children', data.nodeId, data.countryNodeId], data.children);
            var callback = this.get(['callbacks', 'children', data.nodeId, data.countryNodeId]);
            if (callback) {
                callback(data.children);
                this.set(['callbacks', 'children', data.nodeId, data.countryNodeId], false);
            }
        },
        optionsLoaded: function(data) {     // called when data is returned from server
            this.set(['options'], data);
            var callback = this.get(['callbacks', 'options']);
            if (callback) {
                callback(data);
                this.set(['callbacks', 'options'], false);
            }
        },
        getChildren: function(nodeId, countryNodeId, callback) {
            var data = this.get(['children', nodeId, countryNodeId]);
            if (data) {
                callback(data);
            } else {
                this.set(['callbacks', 'children', nodeId, countryNodeId], callback);
                this.loadScript(
                    this.baseUrl + '/loc/script/children/'+ locationService.folderId +'/' + nodeId + '/' + countryNodeId
                        + '?func=locationService.remoteService.childrenLoaded'
                );
            }
        },
        getOptions: function(callback) {
            var options = this.get(['options']);
            if (options) {
                callback(options);
            } else {
                this.set(['callbacks', 'options'], callback);
                this.loadScript(this.baseUrl + '/loc/script/options/'+ locationService.folderId +'?func=locationService.remoteService.optionsLoaded');
            }
        },
        loadScript: function(url) {
            var a = document.createElement("script");
            a.type = "text/javascript";
            a.async = true;
            a.src = url;
            var b = document.getElementsByTagName("script")[0];
            b.parentNode.insertBefore(a, b);
        }
    }
};
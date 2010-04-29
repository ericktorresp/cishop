from django.db import models
from django import forms
from django.forms.widgets import MultiWidget, TextInput


class SeparatedValuesWidget(MultiWidget):
    """
    A Widget that splits input into two <input type="text"> boxes.
    """
    def __init__(self, attrs=None):
        widgets = (TextInput(attrs={'title':'min'}),
                   TextInput(attrs={'title':'max'}))
        super(SeparatedValuesWidget, self).__init__(widgets, attrs)

    def decompress(self, value):
        if value:
            value = eval(value)
            return [unicode(s) for s in value]
        return []    
#    def render(self, name, value, attrs=None):
#        if value is None:
#            value = ""
        

class SeparatedValuesFormField(forms.CharField):
    widget = SeparatedValuesWidget
    
    def __init__(self, *args, **kwargs):
        super(SeparatedValuesFormField, self).__init__(*args, **kwargs)
    
    def clean(self, value):
        value = super(SeparatedValuesFormField, self).clean(value)
        value = eval(value)
        return value
 
class SeparatedValuesField(models.Field):
    __metaclass__ = models.SubfieldBase
 
    def __init__(self, *args, **kwargs):
        self.token = kwargs.pop('token', ',')
        super(SeparatedValuesField, self).__init__(*args, **kwargs)
 
    def to_python(self, value):
        if not value: return
        return value
 
    def get_db_prep_value(self, value):
        if not value: return
        assert(isinstance(value, list) or isinstance(value, tuple))
        return self.token.join([s for s in value])

    def get_db_prep_save(self, value):
        if value is None: return
        return self.token.join(value)
 
    def value_to_string(self, obj):
        value = self._get_val_from_obj(obj)
        return self.get_db_prep_value(value)

    def get_internal_type(self):
        return "CharField"
    
    def formfield(self, **kwargs):
        attrs = {}
        defaults = {
            'form_class': SeparatedValuesFormField,
            'widget': SeparatedValuesWidget(attrs=attrs),
        }
        defaults.update(kwargs)
        return super(SeparatedValuesField, self).formfield(**defaults)

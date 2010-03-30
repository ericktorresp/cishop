# -*- coding: utf-8 -*-
from django.db import models
from channels.models.channel import Channel
#=================
# Configure Model
#=================
VALUE_TYPE = (
              ('num', u'Number'),
              ('string', u'String'),
              )
INPUT_TYPE = (
              ('text', u'Input'),
              ('checkbox', u'Check'),
              ('select', u'Select'),
              )
class Configure(models.Model):
    parent = models.ForeignKey('self', default=0, blank=True, null=True)
    config_key = models.CharField(max_length=30, blank=True)
    config_value = models.CharField(max_length=255, blank=True)
    default_value = models.CharField(max_length=255, blank=True)
    config_value_type = models.CharField(max_length=10, blank=True, choices=VALUE_TYPE)
    form_input_type = models.CharField(max_length=10, blank=True, choices=INPUT_TYPE)
    channel = models.ForeignKey(Channel)
    title = models.CharField(max_length=255)
    description = models.CharField(max_length=255, blank=True)
    is_disabled = models.BooleanField('Disabled?', default=False)
    
    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'channels'

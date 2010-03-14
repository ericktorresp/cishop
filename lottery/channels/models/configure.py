from django.db import models
from channels.models.channel import Channel
#=================
# Configure Model
#=================

class Configure(models.Model):
    parent = models.ForeignKey('self',default=0,blank=True,null=True)
    config_key = models.CharField(max_length=30,blank=True)
    config_value = models.CharField(max_length=255,blank=True)
    default_value = models.CharField(max_length=255,blank=True)
    config_value_type = models.CharField(max_length=10,blank=True)
    form_input_type = models.CharField(max_length=10,blank=True)
    channel = models.ForeignKey(Channel)
    title = models.CharField(max_length=255)
    description = models.CharField(max_length=255,blank=True)
    is_disabled = models.BooleanField('Disabled?',default=False)
    
    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'channels'
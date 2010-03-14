from django.db import models
from channels.models.channel import Channel
#=================
# Configure Model
#=================

class Configure(models.Model):
    parent_id = models.ForeignKey('self')
    config_key = models.CharField(max_length=30)
    config_value = models.CharField(max_length=255)
    default_value = models.CharField(max_length=255)
    config_value_type = models.CharField(max_length=10)
    form_input_type = models.CharField(max_length=10)
    channel = models.ForeignKey(Channel)
    title = models.CharField(max_length=255)
    description = models.CharField(max_length=255)
    is_disabled = models.BooleanField('Disabled?',default=False)
    
    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'channels'
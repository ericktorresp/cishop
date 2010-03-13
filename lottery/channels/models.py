from django.db import models

# Channel model
class Channel(models.Model):
    title = models.CharField(max_length=200)
    path = models.CharField(max_length=200)
    is_disabled = models.BooleanField()
    created_at = models.DateTimeField('Created at')
    updated_at = models.DateTimeField('Updated at')
    
    def __unicode__(self):
        return self.title

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
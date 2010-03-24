from django.db import models
from django.contrib.auth.models import User

# Channel model
class Channel(models.Model):
    title = models.CharField(max_length=200)
    path = models.CharField(max_length=200)
    is_disabled = models.BooleanField()
    created_at = models.DateTimeField('Created at', editable=False, auto_now_add=True)
    updated_at = models.DateTimeField('Updated at', editable=False, auto_now=True)
    usersets = models.ManyToManyField(User, through='UserChannelSet')
        
    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'channels'

class UserChannelSet(models.Model):
    user = models.ForeignKey(User)
    channel = models.ForeignKey(Channel)
    is_disabled = models.BooleanField(default=False)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'channels'

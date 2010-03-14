from django.db import models
from django.contrib.auth.models import User

import datetime

# Channel model
class Channel(models.Model):
    title = models.CharField(max_length=200)
    path = models.CharField(max_length=200)
    is_disabled = models.BooleanField()
    created_at = models.DateTimeField('Created at', editable=False)
    updated_at = models.DateTimeField('Updated at', editable=False)
    usersets = models.ManyToManyField(User)
    
    def save(self):
        if not self.id:
            self.created_at = datetime.datetime.now()
        self.updated_at = datetime.datetime.now()
        super(Channel,self).save()
        
    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'channels'
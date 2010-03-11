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

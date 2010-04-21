from django.db import models

class Province(models.Model):
    title = models.CharField(max_length=100)
    icon = models.CharField(max_length=10)

    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]

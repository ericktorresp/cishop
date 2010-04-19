from django.db import models

class Drug(models.Model):
    title = models.CharField(max_length=100)
    price = models.IntegerField()
    stock = models.IntegerField()
    stamina = models.SmallIntegerField()
    spirit = models.SmallIntegerField()
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    modified = models.DateTimeField('Updated', editable=False, auto_now=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'

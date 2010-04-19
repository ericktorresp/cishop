from django.db import models
from django.contrib.auth.models import User

class Drug(models.Model):
    title = models.CharField(max_length=100)
    price = models.IntegerField()
    stock = models.IntegerField()
    stamina = models.SmallIntegerField()
    spirit = models.SmallIntegerField()
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    modified = models.DateTimeField('Updated', editable=False, auto_now=True)
    user_drug = models.ManyToManyField(User, through='UserDrug')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id']

class UserDrug(models.Model):
    user = models.ForeignKey(User)
    drug = models.ForeignKey(Drug)
    units = models.IntegerField()
    
    def __unicode__(self):
        return self.user.username + '\'s ' + self.drug.title
    
    class Meta:
        db_table = 'user_drug'
        app_label = 'system'

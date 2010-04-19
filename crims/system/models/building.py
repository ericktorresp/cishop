from django.db import models
from filebrowser.fields import FileBrowseField
from system.models.drug import Drug
from django.contrib.auth.models import User

class Building(models.Model):
    title = models.CharField(max_length=100)
    output = models.IntegerField()
    expend = models.IntegerField()
    price = models.IntegerField()
    photo = FileBrowseField(u'Photo', max_length=200, directory="building/", format="Image", extensions=['.jpg', '.gif', '.png'])
    drug = models.ForeignKey(Drug)
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    user_building = models.ManyToManyField(User, through='UserBuilding')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id']

class UserBuilding(models.Model):
    user = models.ForeignKey(User)
    building = models.ForeignKey(Building)
    title = models.CharField(max_length=200, blank=True, null=True)
    created = models.DateTimeField(editable=False, auto_now_add=True)
    modified = models.DateTimeField(editable=False, auto_now=True)
    units = models.IntegerField()
    outputs = models.IntegerField()
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        db_table = 'user_building'
        app_label = 'system'

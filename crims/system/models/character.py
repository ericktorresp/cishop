from django.db import models
from filebrowser.fields import FileBrowseField

class Character(models.Model):
    title = models.CharField(max_length=100)
    avatar = FileBrowseField(u'Avatar', max_length=200, directory="character/", format="Image", extensions=['.jpg', '.gif', '.png'])
    photo = FileBrowseField(u'Photo', max_length=200, directory="character/", format="Image", extensions=['.jpg', '.gif', '.png'])
    intelligence = models.IntegerField(max_length=6)
    strength = models.IntegerField(max_length=6)
    charisma = models.IntegerField(max_length=6)
    tolerance = models.IntegerField(max_length=6)
    created = models.DateTimeField('Created at', editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id']

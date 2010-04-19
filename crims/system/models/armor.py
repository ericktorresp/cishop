from django.db import models
from filebrowser.fields import FileBrowseField

class Armor(models.Model):
    title = models.CharField(max_length=100)
    tolerance = models.IntegerField(max_length=6)
    price = models.IntegerField()
    photo = FileBrowseField(u'Image', max_length=200, directory="armor/", format="Image", extensions=['.jpg', '.gif', '.png'])
    created = models.DateTimeField('Created at', editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'

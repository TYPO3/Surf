===================================================================
Migration for deployment scripts when switching form 0.9.x to 2.0.0
===================================================================

1. Move deployment scripts form `Build/Surf` to `~/.surf/deployments`
#. Rename task or use migrate command to switch to new task names
#. Set `transferMethod` and `packageMethod` options in your application, 
   as the default changed from git to rsync 
#. Change options for `CreateDirectoriesTask`: Now the specified directories are based on the application's release 
   path not the general deployment path (which did not make much sense)

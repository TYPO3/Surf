# Migration for deployment scripts when switching form 0.9.x to 2.0.0

1. Move deployment scripts form `Build/Surf` to `~/.surf/deployments`
1. Rename task or use migrate command to switch to new task names
1. Set `transferMethod` and `packageMethod` options in your application, as the default changed from git to rsync 
1. Change options for `CreateSymlinkTask`: Now the array key is the link source and the array value the link target, to match the command line order.
1. Change options for `CreateDirectoriesTask`: Now the specified directories are based on the application's release path not the general deployment path (which did not make much sense)

[User Manual](index.html)

# Managing files

So far, our running examples did not need any external file to be run; if they did, they were generated dynamically as [prerequisites](experiments.html#prerequisites). However, a laboratory can also be bundled with additional files that can be read by your Java code.

If you are using the [template project](https://github.com/liflab/labpal-project) (which we recommend), the lab's source files are organized like this:

    /labpal-project
      Readme.md
      /Source
        /src
          MyLaboratory.java
          MyExperiment.java
          ...
        /bin

If you with to include data files or other resources in your lab, simply put them somewhere under the `src` folder where your source files are located. We call these files *internal* resources; the way to access these files is slightly different from normal files somewhere else in your computer.

## <a name="filehelper">Using the FileHelper</a>

LabPal contains a class called [FileHelper](/doc/ca/uqac/lif/labpal/FileHelper.html), which provides a number of methods for manipulating files. To access internal resources, you must use either of these methods:

- internalFileToBytes
- internalFileToString
- internalFileToStream

Suppose for example that your lab contains an image file, `bird.jpg`, located directly in the "src" folder. To read that file as an array of bytes, you would do the following:

<pre><code>byte[] contents = FileHelper.internalFileToBytes(MyLaboratory.class, "bird.jpg");
</code>
</pre>

The first argument passed to the method is a class; FileHelper uses this class to fetch the desired file relative to some location in the project. Since MyLaboratory is located in the "src" folder, the file path given as an argument will be relative to this folder.

You can obviously create folders in your project; for example, put the image files into a subfolder called "images":

    /labpal-project
      Readme.md
      /Source
        /src
          MyLaboratory.java
          MyExperiment.java
          /images
            bird.jpg
        /bin

Simply mention the full path to the resource when reading it:

<pre><code>byte[] contents = FileHelper.internalFileToBytes(MyLaboratory.class, "images/bird.jpg");
</code>
</pre>

## <a name="bundle">Bundling internal resources</a>

The template project comes with an Ant build script that compiles the lab. At the command line, simply type:

    $ ant

This will create a JAR file called `my-lab.jar` (or something else if you changed the settings in `config.xml`).

A nice feature of internal resources is that they are bundled **inside the JAR**. As a matter of fact, if you open the file in an archive manager, you will see that all the internal files you put in your `src` folder are there, organized in exactly the same way. Therefore, the JAR file is not just an executable program: it also acts as the archive for your data files.

This greatly simplifies the execution of your lab by somebody else. There is no need for a user to copy files in specific locations before running the experiments: the lab comes with its own internal, mini-filesystem.

<!-- :wrap=soft:mode=markdown: -->
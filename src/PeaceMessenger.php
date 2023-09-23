<?php

namespace ajiho\IlluminateDatabase;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use PhpParser\PrettyPrinter;
use PhpParser\BuilderFactory;
use PhpParser\NodeFinder;


// 我称它为和平管理大师，用于解决illuminate/database和tp6的所有冲突问题
class PeaceMessenger
{

    //公共函数的文件名称
    private $helpersFilePath;
    private $illuminatePath;

    private $parser;

    //助手函数的名称数组
    private $methods;

    private $prettyPrinter;
    private $nameSpace;
    private $helpersNameSpace;

    private $useName;

    public function __construct($illuminatePath)
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->illuminatePath = $illuminatePath;
        $this->helpersFilePath = $this->illuminatePath . '/support/helpers.php';
        $this->methods = $this->getFunName();
        $this->prettyPrinter = new PrettyPrinter\Standard;
        $this->nameSpace = "\\Illuminate\\Support\\";
        $this->helpersNameSpace = "Illuminate\Support";
        $this->useName = "Closure";
    }


    public function run()
    {
        $this->addHelpersFileNameSpace();
        $this->removeHelpersFileExist();
        $this->addNameSpace();

    }


    private function addNameSpace()
    {

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class($this->methods, $this->nameSpace) extends NodeVisitorAbstract {
            private $methods;
            private $nameSpace;

            public function __construct($methods, $nameSpace)
            {
                $this->methods = $methods;
                $this->nameSpace = $nameSpace;
            }

            public function enterNode(Node $node)
            {
                //判断是否是函数调用节点并进行替换
                foreach ($this->methods as $method) {
                    if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name && $node->name->toString() === $method) {
                        $node->name = new Node\Name($this->nameSpace . $method);
                    }
                }
            }
        });
        $fileContents = $this->readPhpFiles($this->illuminatePath);
        foreach ($fileContents as $filePath => $content) {
            if (str_replace('\\', '/', $filePath) != str_replace('\\', '/', $this->helpersFilePath)) {
                //解析
                $ast = $this->parser->parse($content);
                $ast = $traverser->traverse($ast);
                $this->filePutContents($filePath, $ast);
            }
        }
    }


    private function readPhpFiles($directory)
    {
        $contents = [];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $fileInfo) {
            $filePath = $fileInfo->getPathname();

            // 只处理 PHP 文件
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                $content = file_get_contents($filePath);
                $contents[$filePath] = $content;
            }
        }

        return $contents;
    }


    private function getFunName()
    {
        try {
            // 解析 PHP 代码
            $ast = $this->getHelpersFileAst();

            // 遍历 AST 并提取函数名称
            $traverser = new NodeTraverser();
            $visitor = new class extends NodeVisitorAbstract {
                public $functionNames = [];

                public function enterNode(Node $node)
                {
                    if ($node instanceof Function_) {
                        $this->functionNames[] = $node->name->toString();
                    }
                }
            };
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);
            return $visitor->functionNames;

        } catch (Error $error) {
            return [];
        }
    }


    private function addHelpersFileNameSpace()
    {
        $ast = $this->getHelpersFileAst();


        //查找是否存在这个类名空间
        $finder = new NodeFinder();
        $namespaceNode = $finder->findFirstInstanceOf($ast, Namespace_::class);
        if ($namespaceNode === null) { // 没有命名空间,则添加这个命名空间
            $factory = new BuilderFactory();
            // 创建新的命名空间节点
            $namespace = $factory->namespace($this->helpersNameSpace)
                ->addStmt($factory->use($this->useName)->getNode())
                ->addStmts($ast)
                ->getNode();

            $this->filePutContents($this->helpersFilePath, [$namespace]);
        }
    }

    private function removeHelpersFileExist()
    {

        $ast = $this->getHelpersFileAst();

        // 创建节点遍历器和节点访问器
        $traverser = new NodeTraverser();
        // 添加节点访问器到遍历器中
        $traverser->addVisitor(new class extends NodeVisitorAbstract {
            public function leaveNode(Node $node)
            {
                // 判断节点类型是否为目标条件语句
                if ($node instanceof If_
                    && count($node->stmts) === 1
                    && $node->stmts[0] instanceof Function_
                ) {
                    // 返回函数体节点
                    return $node->stmts[0];
                }
                return $node;
            }
        });

        // 遍历 AST 并修改节点
        $modifiedStmts = $traverser->traverse($ast);
        $this->filePutContents($this->helpersFilePath, $modifiedStmts);
    }


    private function filePutContents($filepath, $stmts)
    {
        file_put_contents($filepath, $this->prettyPrinter->prettyPrintFile($stmts));
    }

    private function getHelpersFileAst()
    {
        return $this->parser->parse(file_get_contents($this->helpersFilePath));
    }

}

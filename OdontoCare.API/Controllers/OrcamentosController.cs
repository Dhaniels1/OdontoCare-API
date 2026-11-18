using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common.Models;
using OdontoCare.Application.Common.Security;
using OdontoCare.Application.Features.Financeiro.DTOs;
using OdontoCare.Application.Features.Financeiro.Interfaces;

namespace OdontoCare.API.Controllers;
[ApiController, Route("api/orcamentos"), Authorize(Roles = Roles.Todos)]
public sealed class OrcamentosController(IOrcamentoService service) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> Listar() => Ok(ResultWrapper<List<OrcamentoResponseDto>>.Ok(await service.ListarAsync()));
    [HttpGet("{id:int}")]
    public async Task<IActionResult> Buscar(int id) => await service.BuscarAsync(id) is { } item
        ? Ok(ResultWrapper<OrcamentoResponseDto>.Ok(item)) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
    [HttpPost, Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Criar(OrcamentoGravarDto dto)
    {
        var item=(await service.GravarAsync(null,dto))!;
        return CreatedAtAction(nameof(Buscar), new { id=item.IdOrcamento }, ResultWrapper<OrcamentoResponseDto>.Ok(item));
    }
    [HttpPut("{id:int}"), Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Atualizar(int id, OrcamentoGravarDto dto) => await service.GravarAsync(id,dto) is { } item
        ? Ok(ResultWrapper<OrcamentoResponseDto>.Ok(item)) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
    [HttpDelete("{id:int}"), Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Excluir(int id) => await service.ExcluirAsync(id)
        ? Ok(ResultWrapper<object>.Ok(null,"Registro excluído.")) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
}
